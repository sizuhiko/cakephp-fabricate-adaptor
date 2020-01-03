<?php
namespace CakeFabricate\Test;

use CakeFabricate\Adaptor\CakeFabricateAdaptor;
use CakeFabricate\Test\App\Model\Table\PostsTable;
use CakeFabricate\Test\App\Model\Table\UsersTable;

use Fabricate\Fabricate;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Fabricate class test case
 */
class CakeFabricateTest extends TestCase {
    public $fixtures = ['plugin.CakeFabricate.Posts', 'plugin.CakeFabricate.Users'];

    public function setUp(): void {
        parent::setUp();
        Fabricate::clear();
        Fabricate::config(function($config) {
            $config->adaptor = new CakeFabricateAdaptor();
        });
    }

    public function testAttributesFor() {
        $results = Fabricate::attributes_for('Posts', 10, function($data){
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
        $result = Fabricate::build('Posts', function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $this->assertInstanceOf('CakeFabricate\Test\App\Model\Entity\Post', $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(1, $result->author_id);
        $this->assertEquals(50, strlen($result->title));
        $this->assertNotEmpty($result->body);
        $this->assertEquals(1, strlen($result->published));
        $this->assertEquals('2013-10-09 12:40:28', $result->created->i18nFormat('YYYY-MM-dd HH:mm:ss'));
        $this->assertEquals('2013-10-09 12:40:28', $result->updated->i18nFormat('YYYY-MM-dd HH:mm:ss'));
    }

    public function testBuildErrorIfValidateTrue() {
        Fabricate::config(function($config) {
            $config->adaptor = new CakeFabricateAdaptor([CakeFabricateAdaptor::OPTION_VALIDATE => true]);
        });
        $result = Fabricate::build('Posts', function($data){
            return ["title" => ""];
        });
        $this->assertArrayHasKey('title', $result->getErrors());
    }

    public function testCreate() {
        Fabricate::create('Posts', 10, function($data){
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
            $this->assertEquals('2013-10-09 12:40:28', $post->created->i18nFormat('YYYY-MM-dd HH:mm:ss'));
            $this->assertEquals('2013-10-09 12:40:28', $post->updated->i18nFormat('YYYY-MM-dd HH:mm:ss'));
        }
    }

    public function testAttributeForAutoPrimaryKey() {
        Fabricate::config(function($config) {
            $config->adaptor = new CakeFabricateAdaptor([CakeFabricateAdaptor::OPTION_FILTER_KEY => true]);
        });
        $results = Fabricate::attributes_for('Posts', function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $this->assertArrayNotHasKey('id', $results);
    }

    public function testCreateAutoPrimaryKey() {
        Fabricate::config(function($config) {
            $config->adaptor = new CakeFabricateAdaptor([CakeFabricateAdaptor::OPTION_FILTER_KEY => true]);
        });
        Fabricate::create('Posts', function($data){
            return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
        });
        $posts = TableRegistry::get('Posts')->find('all');

        $this->assertCount(1, $posts);
        $this->assertEquals(1, $posts->first()->id);
    }

    public function testSaveWithAssociation() {
        Fabricate::create('Users', function($data, $world) {
            return [
                'username' => 'taro',
                'posts' => $world->association('Posts', 3, ['author_id'=>false]),
            ];
        });

        // TableRegistry::get('Users')->connection()->logQueries(true);
        $user = TableRegistry::get('Users')->find('all')->contain(['Posts'])->first();
        $this->assertEquals('taro', $user->username);
        $this->assertCount(3, $user->posts);
    }

    public function testSaveWithDefinedAssociation() {
        Fabricate::define(['PublishedPost', 'class'=>'Posts'], ['published'=>'1']);
        Fabricate::create('Users', function($data, $world) {
            return [
                'username' => 'taro',
                'posts' => $world->association(['PublishedPost', 'association'=>'Posts'], 3, ['author_id'=>false]),
            ];
        });

        $user = TableRegistry::get('Users')->find('all')->contain(['Posts'])->first();
        $this->assertEquals('taro', $user->username);
        $this->assertCount(3, $user->posts);
    }

    public function testSaveWithBelongsToAssociation() {
        Fabricate::define(['PublishedPost', 'class'=>'Posts'], ['published'=>'1']);
        Fabricate::create('PublishedPost', 3, function($data, $world) {
            return [
                'author' => $world->association(['Users', 'association'=>'Author'], ['id'=>1,'username'=>'taro']),
            ];
        });

        $user = TableRegistry::get('Users')->find('all')->contain(['Posts'])->first();
        $this->assertEquals('taro', $user->username);
        $this->assertCount(3, $user->posts);
    }

    public function testDefineAndAssociationAndTraits() {
        Fabricate::define(['trait'=>'published'], ['published'=>'1']);
        Fabricate::define(['PublishedPost', 'class'=>'Posts'], function($data, $world) {
            $world->traits('published');
            return ['title'=>$world->sequence('title',function($i) { return "Title{$i}"; })];
        });
        Fabricate::create('Users', function($data, $world) {
            return [
                'username' => 'taro',
                'posts' => Fabricate::association('PublishedPost', 3, ['author_id'=>false]),
            ];
        });

        $user = TableRegistry::get('Users')->find('all')->contain(['Posts'])->first();
        $this->assertEquals('taro', $user->username);
        $this->assertCount(3, $user->posts);
        $this->assertEquals(['1','1','1'], array_map(function($post) { return $post->published; }, $user->posts));
        $this->assertEquals(['Title1','Title2','Title3'], array_map(function($post) { return $post->title; }, $user->posts));
    }

}
