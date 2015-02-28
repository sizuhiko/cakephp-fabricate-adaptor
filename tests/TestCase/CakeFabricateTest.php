<?php
namespace CakeFabricate\Test;

use CakeFabricate\Test\App\Model\Table\PostsTable;
use CakeFabricate\Test\App\Model\Table\UsersTable;

use Fabricate\Fabricate;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Fabricate class test case
 */
class CakeFabricateTest extends TestCase {
    public $fixtures = ['plugin.cake_fabricate.posts', 'plugin.cake_fabricate.users'];

    public function setUp() {
        parent::setUp();
        Fabricate::clear();
    }

    public function testAttributesFor() {
        $results = Fabricate::attributes_for('Post', 10, function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $this->assertCount(10, $results);
        for ($i = 0; $i < 10; $i++) { 
            $this->assertEquals($i+1, $results[$i]['id']);
            $this->assertEquals($i+1, $results[$i]['author_id']);
            $this->assertEquals(50, strlen($results[$i]['title']));
            $this->assertNotEmpty($results[$i]['body']);
            $this->assertEquals(1, strlen($results[$i]['published']));
            $this->assertEquals('2013-10-09 12:40:28', $results[$i]['created']);
            $this->assertEquals('2013-10-09 12:40:28', $results[$i]['updated']);
        }
    }

    public function testBuild() {
        $result = Fabricate::build('Post', function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $this->assertInstanceOf('Post', $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(1, $result->author_id);
        $this->assertEquals(50, strlen($result->title));
        $this->assertNotEmpty($result->body);
        $this->assertEquals(1, strlen($result->published));
        $this->assertEquals('2013-10-09 12:40:28', $result->created);
        $this->assertEquals('2013-10-09 12:40:28', $result->updated);
    }

    public function testCreate() {
        Fabricate::create('Post', 10, function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $posts = TableRegistry::get('Posts')->find('all');

        $this->assertCount(10, $posts);
        foreach ($posts as $i => $post) {
            $this->assertEquals($i+1, $post->id);
            $this->assertEquals($i+1, $post->author_id);
            $this->assertEquals(50, strlen($post->title));
            $this->assertNotEmpty($post->body);
            $this->assertEquals(1, strlen($post->published));
            $this->assertEquals('2013-10-09 12:40:28', $post->created);
            $this->assertEquals('2013-10-09 12:40:28', $post->updated);
        }
    }

    public function testSaveWithAssociation() {
        Fabricate::create('User', function($data, $world) {
            return [
                'user' => 'taro',
                'Post' => $world->association('Post', 3, ['id'=>false,'author_id'=>false]),
            ];
        });

        $user = TableRegistry::get('Users')->find('first')->contain(['Post']);
        $this->assertEquals('taro', $user->user);
        $this->assertCount(3, $user->posts);
    }

    public function testSaveWithDefinedAssociation() {
        Fabricate::define(['PublishedPost', 'class'=>'Post'], ['published'=>'1']);
        Fabricate::create('User', function($data, $world) {
            return [
                'user' => 'taro',
                'Post' => $world->association(['PublishedPost', 'association'=>'Post'], 3, ['id'=>false,'author_id'=>false]),
            ];
        });

        $user = TableRegistry::get('Users')->find('first')->contain(['Post']);
        $this->assertEquals('taro', $user->user);
        $this->assertCount(3, $user->posts);
    }

    public function testSaveWithBelongsToAssociation() {
        Fabricate::define(['PublishedPost', 'class'=>'Post'], ['published'=>'1']);
        Fabricate::create('PublishedPost', 3, function($data, $world) {
            return [
                'Author' => $world->association(['User', 'association'=>'Author'], ['id'=>1,'user'=>'taro']),
            ];
        });

        $user = TableRegistry::get('Users')->find('first')->contain(['Post']);
        $this->assertEquals('taro', $user->user);
        $this->assertCount(3, $user->posts);
    }

    public function testDefineAndAssociationAndTraits() {
        Fabricate::define(['trait'=>'published'], ['published'=>'1']);
        Fabricate::define(['PublishedPost', 'class'=>'Post'], function($data, $world) {
            $world->traits('published');
            return ['title'=>$world->sequence('title',function($i) { return "Title{$i}"; })];
        });
        Fabricate::create('User', function($data, $world) {
            return [
                'user' => 'taro',
                'Post' => Fabricate::association('PublishedPost', 3, ['id'=>false,'author_id'=>false]),
            ];
        });

        $user = TableRegistry::get('Users')->find('first')->contain(['Post']);
        $this->assertEquals('taro', $user->user);
        $this->assertCount(3, $user->posts);
        $this->assertEquals(['1','1','1'], array_map(function($post) { return $post->publichsed; }));
        $this->assertEquals(['Title1','Title2','Title3'], array_map(function($post) { return $post->title; }));
    }

}
