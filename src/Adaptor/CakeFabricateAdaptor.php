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
     * @inherit
     */
    public function getModel($modelName)
    {
        $model = new FabricateModel($modelName);
        $table = TableRegistry::get($modelName);
        $schema = $table->schema();
        foreach ($schema->columns() as $name) {
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
        $entities = $table->newEntities($attributes);
        foreach ($entities as $entity) {
            $ret = $table->save($entity);
            if(!$ret) {
                var_dump($entity);
            }
        }
        return $entities;
    }

    /**
     * @inherit
     */
    public function build($modelName, $data)
    {
        $table = TableRegistry::get($modelName);
        $entity = $table->newEntity($data);
        return $entity;
    }
}
