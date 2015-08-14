<?php
namespace CakeFabricate\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Post Entity.
 */
class Post extends Entity
{
    protected $_accessible = [
        'title' => true,
        'body' => true,
        'published' => true,
        'created' => true,
        'updated' => true
    ];
}
