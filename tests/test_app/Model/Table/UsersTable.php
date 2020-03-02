<?php
namespace CakeFabricate\Test\App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 */
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Posts', [
            'className' => 'CakeFabricate\Test\App\Model\Table\PostsTable',
            'foreignKey' => 'author_id',
            'propertyName' => 'posts'
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
            ->requirePresence('username', 'create')
            ->notEmptyString('username')
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        return $validator;
    }
}
