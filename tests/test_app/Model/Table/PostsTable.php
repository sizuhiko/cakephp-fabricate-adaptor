<?php
namespace CakeFabricate\Test\App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Posts Model
 */
class PostsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('posts');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Author', [
            'className' => 'CakeFabricate\Test\App\Model\Table\UsersTable',
            'foreignKey' => 'author_id',
            'propertyName' => 'author'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmptyString('id', 'create')
            ->requirePresence('title', 'create')
            ->notEmptyString('title')
            ->requirePresence('body', 'create')
            ->notEmptyString('body');

        return $validator;
    }
}
