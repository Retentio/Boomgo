Boomgo, a micro PHP ODM for [MongoDB](http://www.mongodb.org/)
==============================================================

_Boomgo still a work in progress and is initially developped for [Retentio](http://retent.io)_

Boomgo is a **light** and **simple** Object Document Mapper on top of the [MongoDB php native driver](http://php.net/mongo).

[![Build Status](https://secure.travis-ci.org/Retentio/Boomgo.png)](http://travis-ci.org/Retentio/Boomgo)

Philosophy
-------------

Boomgo ODM focuses on the mapping process between PHP objects and MongoDB Documents, It doesn't abstract any feature provided by the native php driver. This way, Boomgo allows you to **keep the full control about your MongoDB interactions** (querying, map reduce, ...).

_In short, Boomgo offers a handy way to manipulate your MongoDB Documents with PHP Objects._

Features
--------

Boomgo generate Mappers for your php object, which allow you to:

* Hydrate PHP Object from a MongoDB results set.
* Serialize PHP Object to mongo-storable array.
* Handle hydration process of embedded document / collection.

Requirements
------------

Boomgo was built with a lot of love (including best practices & standards).
It will only work for **PHP 5.3+ projects** which use a **structure matching** [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
Furthermore, [composer](http://getcomposer.org/) usage is strongly encouraged.

Installation
------------

### Composer

First, in your composer.json, add the requirement line for Boomgo.

```json
{
    "require": {
        "retentio/boomgo": "dev-master"
    }
}
```

Then get composer and run the install command.

```bash
$ wget -nc -nv http://getcomposer.org/composer.phar
$ php composer.phar install
```

Usage
-----

At the moment, Boomgo supports only annotation definition. Yet **it uses only a single tag**: by default "@Persistent" (you can change it).
To persist some attributes of your model, Boomgo needs 3 things :

1. A dedicated & unique namespace part for your persisted classes (default "Document").
1. The "@Persistent" tag in the property docblock.
2. Getter & Setter for this property.


### Simple persistence

```php
<?php

namespace VendorName\Project\Document;

class MyPersistedClass
{

    /**
     * @Persistent
     */
    private $myField

    public function getMyField()
    {
        return $this->myField;
    }

    public function setMyField($value)
    {
        $this->myField = $value;
    }
}

?>
```

Then, you can generate your mapper with the command (if you used composer):

```bash
$ vendor/bin/boomgo generate:mappers path/to/your/document/folder
```

Boomgo will generate (default: aside of your documents folder) a mapper class called `VendorName\Project\Mapper\MyPersistedClassMapper`.
The mapper exposes 3 methods:

* `->serialize($yourObject)`
* `->unserialize($yourArray)`
* `->hydrate($yourObject, $yourArray)`

Then, the usage becomes really simple:

```php
<?php

// Create your connection with the native mongoDB php driver
$mongo = new \MongoClient("mongodb://127.0.0.1:27017");

// Create your object
$object = new \VendorName\Project\Document\MyPersistedClass();
$object->setMyField('my value');

// Create the mapper
$mapper = new \VendorName\Project\Mapper\MyPersistedClassMapper();

// Serialize your object to a mongoable array
$mongoableArray = $mapper->serialize($object);

// Save with the native php driver
$mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->save($mongoableArray);

// Fetch a result with the native driver
$result = $mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->findOne(array('myField' => 'my value'));

// Unserialize the result to an object
$object = $mapper->unserialize($result);
$object->getMyField();

// You could also hydrate an existing object from a result
$object = new \VendorName\Project\Document\MyPersistedClass();
$mapper->hydrate($object, $result);

?>
```

### Advanced persistence

Boomgo handles **native PHP Mongo types** (MongoId, etc.), **embedded documents** and **nested collections**.
Since, Boomgo love simple & efficient things, annotation are not used for that. Instead it rely on... docblock with the famous under-used @var tag.

```php
<?php

namespace VendorName\Project\Document;

class DocumentClass
{
    /**
     * @Persistent
     * @var \MongoId
     */
    private $id // expect a MongoId native instance

    /**
     * @Persistent
     * @var string
     */
    private $myField // scalar type should be specified, avoid normalization process

    /**
     * @Persistent
     * @var array
     */
    private $myArray // composite type will be normalized

    /**
     * @Persistent
     */
    private $myVar // Default type will be "mixed" and processed as a composite type

    /**
     * @Persistent
     * @var VendorName\Project\EmbeddedDocument
     */
    private $embeddedDocument // a single embedded document

    /**
     * @Persistent
     * @var array [VendorName\Project\EmbeddedDocument]
     */
    private $embeddedCollection // many embedded documents the "[ ]" chars are mandatory

    // getters & setters
}

?>
```

After mapper generation, usage is almost the same and stay explicit, Boomgo doesn't hide magic.

```php
<?php

// Create your connection with the native mongoDB php driver
$mongo = new \MongoClient("mongodb://127.0.0.1:27017");

// Create your object
$object = new \VendorName\Project\Document\DocumentClass();
$object->setId(new \MongoId());
$object->setMyField('my value');
$object->setMyArray(array('many','values'));
$object->setMyVar('anything');
$object->setEmbeddedDocument(new \VendorName\Project\Document\EmbeddedDocument());
$object->setEmbeddedCollection(array(new \VendorName\Project\Document\EmbeddedDocument()));

// Create the mapper
$mapper = new \VendorName\Project\Mapper\DocumentClassMapper();

// Serialize your object to a mongoable array
$mongoableArray = $mapper->serialize($object);

// Save with the native php driver
$mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->save($mongoableArray);

// Fetch a result with the native driver
$result = $mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->findOne(array('myField' => 'my value'));

// Unserialize the result to an object
$object = $mapper->unserialize($result);

?>
```

To see the full list of supported type/pseudo type in the @var tag you can look at [Boomgo\Builder\Definition](https://github.com/Retentio/Boomgo/blob/master/src/Boomgo/Builder/Definition.php#L39)
Note that Boomgo won't cast or validate anything, it's only used in the mapping process for normalization & nested documents/collections.

Limitations
-----------

* It doesn't & won't manage object relation for a simple reason: [MongoDB is a NON-RELATIONAL storage](http://www.mongodb.org/display/DOCS/Database+References).
* It doesn't & won't provide [identity map](http://en.wikipedia.org/wiki/Identity_map) etc.
* It doesn't & won't make coffee.

If you're looking for full-featured php ODM, you should look at [Mandango](https://github.com/mandango/mandango) which use active record/class generator implementation, and also [Doctrine MongoDB ODM](http://www.doctrine-project.org/projects/mongodb_odm/current/docs/en) data-mapping implementation.

Known issues
------------

* Only MongoId native type is supported, yet it's really easy to add and test other native type.
* Boomgo doesn't fit totally the PSR-0 (actually do not handle underscored class name)
* Boomgo formatters need improvement/refacto


Roadmap
-------

* Provide a manager.
* Add functional tests.
* More parsers (yml, xml and json).
* ActiveRecord implementation.
* Provide more alternatives for mappers generation (like flat file structures).
* Document classes generation (getters & setters, JsonSerializable interface from php 5.4).
* Json document preview.
* Dynamic mapping using live discrimination.

Feel free to contribute !

How to run unit tests
---------------------

Boomgo is unit tested with [atoum](https://github.com/mageekguy/atoum), the dependency is not shipped by default, with composer you have to run the command

```bash
$ php composer.phar install --dev --prefer-source
```

To run the complete test suite, open a shell and type :

```bash
$ cd path/to/Boomgo
$ php vendor/bin/atoum -c .atoum.php -d tests
```

Want to test on a single class while contributing ? Here is an example with _AnnotationParser_ class :

```bash
$ php vendor/bin/atoum -c .atoum.php -f tests/Boomgo/Tests/Units/Parser/AnnotationParser.php
```

Framework integration
---------------------

Boomgo already have integration for:

* Symfony2 with [PlemiBoomgoBundle](https://github.com/Plemi/PlemiBoomgoBundle)

Credits
-------

Boomgo was built thanks to many open source projects & some awesome guys:

* [Atoum](https://github.com/mageekguy/atoum): the KISS framework for unit test.
* [Composer](http://getcomposer.org/): the awesome dependency manager.
* [Symfony](http://symfony.com/): the components lib is a time-saver.
* [Twig Generator](https://github.com/cedriclombardot/TwigGenerator): the cool tool for code generation with twig.
* [Doctrine](http://www.doctrine-project.org/), [Mandango](http://mandango.org/): ORM/ODM inspiration.
* [@willdurand](https://github.com/willdurand), helped me with a lot of tips.
* [@jpetitcolas](https://github.com/jpetitcolas), regex master.
* [@Palleas](https://github.com/Palleas), ninja supporter forever.