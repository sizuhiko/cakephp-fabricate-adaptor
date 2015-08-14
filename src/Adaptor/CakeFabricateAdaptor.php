<?php
/**
 * CakeFabricate
 *
 * @package    CakeFabricate
 * @subpackage CakeFabricate\Adaptor
 */
namespace CakeFabricate\Adaptor;

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
        $table = TableRegistry::get($modelName);
        $schema = $table->schema();
        foreach ($schema->columns() as $name) {
            if ($this->filterKey($table, $name)) {
                continue;
            }
            $attrs = $schema->column($name);
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
            $target = $association->target();
            $className = get_class($target);
            $alias = $target->alias();
            switch ($association->type()) {
                case Association::ONE_TO_ONE:
                    $model->hasOne($alias, $association->foreignKey(), $className);
                    break;
                case Association::ONE_TO_MANY:
                    $model->hasMany($alias, $association->foreignKey(), $className);
                    break;
                case Association::MANY_TO_ONE:
                    $model->belongsTo($alias, $association->foreignKey(), $className);
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
        $table = TableRegistry::get($modelName);
        $entities = $table->newEntities($attributes, ['validate' => $this->options[self::OPTION_VALIDATE], 'accessibleFields' => ['*' => true]]);
        $table->connection()->transactional(function () use ($table, $entities) {
            foreach ($entities as $entity) {
                $ret = $table->save($entity, [
                    'checkRules' => $this->options[self::OPTION_CHECK_RULES],
                    'atomic' => false
                ]);
                if (!$ret) {
                    return false;
                }
            }
            return true;
        });
        return $entities;
    }

    /**
     * @inherit
     */
    public function build($modelName, $data)
    {
        $table = TableRegistry::get($modelName);
        $entity = $table->newEntity($data, ['validate' => $this->options[self::OPTION_VALIDATE]]);
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
        $primaryKey = $table->primaryKey();
        if (!is_array($primaryKey)) {
            $primaryKey = [$primaryKey];
        }
        return in_array($name, $primaryKey);
    }
}
