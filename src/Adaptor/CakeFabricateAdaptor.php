<?php
/**
 * CakeFabricate
 *
 * @package    CakeFabricate
 * @subpackage CakeFabricate\Adaptor
 */
namespace CakeFabricate\Adaptor;

use Cake\ORM\Locator\LocatorAwareTrait;
use Fabricate\Adaptor\AbstractFabricateAdaptor;
use Fabricate\Model\FabricateModel;

use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;
use Cake\ORM\Association;

/**
 * Fabricate CakePHP Model Adaptor
 */
class CakeFabricateAdaptor extends AbstractFabricateAdaptor
{
    use LocatorAwareTrait;

    /**
     * Filter primary key option.
     * Default setting is false that primary key sets by Fabricate.
     */
    const OPTION_FILTER_KEY = "filter_key";
    /**
     * Validate option.
     * Default setting is false.
     * If you want to validate each entity, set true.
     */
    const OPTION_VALIDATE = "validate";
    /**
     * Application rles option.
     * Default setting is false.
     * If you want to check rules for each entity, set true.
     */
    const OPTION_CHECK_RULES = "checkRules";

    /** option values */
    private $options;

    /**
     * Constructor
     * @param array $options CakeFabricateAdaptor options
     */
    public function __construct($options = [])
    {
        $defaults = [
            self::OPTION_FILTER_KEY => false,
            self::OPTION_VALIDATE   => false,
            self::OPTION_CHECK_RULES      => false,
        ];
        $this->options = array_merge($defaults, $options);
    }

    /**
     * @inherit
     */
    public function getModel($modelName)
    {
        $model = new FabricateModel($modelName);
        $table = $this->getTableLocator()->get($modelName);
        $schema = $table->getSchema();
        foreach ($schema->columns() as $name) {
            if ($this->filterKey($table, $name)) {
                continue;
            }
            $attrs = $schema->getColumn($name);
            $options = [];
            if (array_key_exists('length', $attrs)) {
                $options['limit'] = $attrs['length'];
            }
            if (array_key_exists('null', $attrs)) {
                $options['null'] = $attrs['null'];
            }
            $model->addColumn($name, $attrs['type'], $options);
        }
        foreach ($table->associations()->keys() as $key) {
            $association = $table->associations()->get($key);
            $target = $association->getTarget();
            $className = get_class($target);
            $alias = $target->getAlias();
            switch ($association->type()) {
                case Association::ONE_TO_ONE:
                    $model->hasOne($alias, $association->getForeignKey(), $className);
                    break;
                case Association::ONE_TO_MANY:
                    $model->hasMany($alias, $association->getForeignKey(), $className);
                    break;
                case Association::MANY_TO_ONE:
                    $model->belongsTo($alias, $association->getForeignKey(), $className);
                    break;
            }
        }
        return $model;
    }

    /**
     * @inherit
     */
    public function create($modelName, $attributes, $recordCount)
    {
        $table = $this->getTableLocator()->get($modelName);
        $entities = $table->newEntities($attributes, [
            'validate' => $this->options[self::OPTION_VALIDATE],
            'accessibleFields' => ['*' => true]
        ]);
        $table->saveMany($entities, [
            'checkRules' => $this->options[self::OPTION_CHECK_RULES],
        ]);

        return $entities;
    }

    /**
     * @inherit
     */
    public function build($modelName, $data)
    {
        $table = $this->getTableLocator()->get($modelName);
        $entity = $table->newEntity($data, [
            'validate' => $this->options[self::OPTION_VALIDATE],
            'accessibleFields' => ['*' => true]
        ]);
        return $entity;
    }

    /**
     * Filter key
     *
     * @param string $name field name
     * @return true if $name is primary key, otherwise false
     */
    protected function filterKey($table, $name)
    {
        if (!$this->options[self::OPTION_FILTER_KEY]) {
            return false;
        }
        $primaryKey = $table->getPrimaryKey();
        if (!is_array($primaryKey)) {
            $primaryKey = [$primaryKey];
        }
        return in_array($name, $primaryKey);
    }
}
