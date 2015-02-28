<?php
namespace CakeFabricate\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PostsFixture
 *
 * @package       CakeFabricate\Test\Fixture
 */
class PostsFixture extends TestFixture {

/**
 * fields property
 *
 * @var array
 */
    public $fields = [
        'id' =>        ['type' => 'integer', 'key' => 'primary'],
        'author_id' => ['type' => 'integer', 'null' => false],
        'title' =>     ['type' => 'string', 'null' => false, 'length' => 50],
        'body' =>      'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        'created' =>   'datetime',
        'updated' =>   'datetime'
    ];
}
