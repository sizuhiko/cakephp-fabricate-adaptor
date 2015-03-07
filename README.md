[![Build Status](https://travis-ci.org/sizuhiko/cakephp-fabricate-adaptor.svg?branch=master)](https://travis-ci.org/sizuhiko/Fabricate) [![Coverage Status](https://coveralls.io/repos/sizuhiko/cakephp-fabricate-adaptor/badge.svg?branch=master)](https://coveralls.io/r/sizuhiko/cakephp-fabricate-adaptor?branch=master) [![Total Downloads](https://poser.pugx.org/sizuhiko/cake_fabricate/downloads.svg)](https://packagist.org/packages/sizuhiko/cake_fabricate) [![Latest Stable Version](https://poser.pugx.org/sizuhiko/cake_fabricate/v/stable.svg)](https://packagist.org/packages/sizuhiko/cake_fabricate)


# CakeFabricate plugin for CakePHP

CakeFabricate is adaptor for [Fabricate](https://github.com/sizuhiko/Fabricate/tree/v2), and integrate Fabricate(version 2) to CakePHP3.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require sizuhiko/cake_fabricate
```

## Usage

At first, Fabricate require to config for using.
In app/tests/bootstrap.php, add followings :

```php
use Fabricate\Fabricate;
use CakeFabricate\Adaptor\CakeFabricateAdaptor;

Fabricate::config(function($config) {
    $config->adaptor = new CakeFabricateAdaptor();
});
```

## APIs

CakeFabricateAdaptor has options.
The options set with constructor.

### Configuration

```
Fabricate::config(function($config) {
    $config->adaptor = new CakeFabricateAdaptor([
        CakeFabricateAdaptor::OPTION_FILTER_KEY => true
        CakeFabricateAdaptor::OPTION_VALIDATE   => true
    ]);
});
```

#### Supported Options

##### CakeFabricateAdaptor::OPTION_FILTER_KEY

OPTION_FILTER_KEY If true, not generate any primary key for auto incrementation id.

`Default: false`

##### OPTION_VALIDATE

Indicates whether or not to validate when create new entity.
see: CakePHP's Entity::newEntity()

`Default: false`

### Generate model attributes as array (not saved)

`Fabricate::attributes_for(:name, :number_of_generation, :array_or_callback)` generate only attributes.

* name: Table class alias. For get table instance of CakePHP3, called `TableRegistry::get(:name);`.
* number_of_generation: Generated number of records
* array_or_callback: it can override each generated attributes

#### Example App

Entities:

- Model\Entity\Post
- Model\Entity\User

Tables:

- Model\Entity\PostsTable
- Model\Entity\UsersTable

Associations:

PostsTable has many UsersTable as Author

#### Example

```php
$results = Fabricate::attributes_for('Posts', 10, function($data){
    return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
});

// $results is array followings :
array (
  0 => 
  array (
    'title' => 'Lorem ipsum dolor sit amet',
    'body' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
    'created' => '2013-10-09 12:40:28',
    'updated' => '2013-10-09 12:40:28',
  ),
  1 => 
  array (
  ....
```

### Generate a entity instance (not saved)

`Fabricate::build(:name, :array_or_callback)` generate a entity instance (using Table::newInstance).

* name: Table class alias. For get table instance of CakePHP3, called `TableRegistry::get(:name);`.
* array_or_callback: it can override each generated attributes

#### Example

```php
$result = Fabricate::build('Posts', function($data){
    return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
});

// $result a Model\Entity\Post object.
 ......
```

### Generate records to database

`Fabricate::create(:name, :number_of_generation, :array_or_callback)` generate and save records to database.

* name: Table class alias. For get table instance of CakePHP3, called `TableRegistry::get(:name);`.
* number_of_generation: Generated number of records
* array_or_callback: it can override each generated attributes

#### Example

```php
Fabricate::create('Posts', 10, function($data){
    return ["created" => "2013-10-09 12:40:28", "updated" => "2013-10-09 12:40:28"];
});
```

## Associations

It's possible to set up associations(hasOne/hasMany/belongsTo) within Fabricate::create().
You can also specify a FabricateContext::association().
It will generate the attributes, and set(merge) it in the current array. 

### Usage

```php
Fabricate::create('Users', function($data, $world) {
    return [
        'user' => 'taro',
        'posts' => $world->association('Posts', 3, ['author_id'=>false]),
    ];
});
// can use defined onbject.
Fabricate::define(['PublishedPost', 'class'=>'Posts'], ['published'=>'1']);
Fabricate::create('Users', function($data, $world) {
    return [
        'user' => 'taro',
        'posts' => $world->association(['PublishedPost', 'association'=>'Posts'], 3, ['author_id'=>false]),
    ];
});
// can use association alias (Post belongs to Author of User class)
Fabricate::define(['PublishedPost', 'class'=>'Posts'], ['published'=>'1']);
Fabricate::create('PublishedPost', 3, function($data, $world) {
    return [
        'author' => $world->association(['Users', 'association'=>'Author'], ['id'=>1,'user'=>'taro']),
    ];
});
```

## Any more features

Please see documentation of [Fabricate](https://github.com/sizuhiko/Fabricate/tree/v2).

## Contributing to this Library

Please feel free to contribute to the library with new issues, requests, unit tests and code fixes or new features.
If you want to contribute some code, create a feature branch from develop, and send us your pull request.
