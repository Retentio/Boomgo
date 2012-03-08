Boomgo, a micro PHP ODM for [MongoDB](http://www.mongodb.org/) [![Build Status](https://secure.travis-ci.org/Retentio/Boomgo.png)](http://travis-ci.org/Retentio/Boomgo)
=============================================================================================================================

_Boomgo still a work in progress and is initially developped for [Retentio](http://retent.io)._

Boomgo is a **light** and **simple** Object Document Mapper on top of the [MongoDB php native driver](http://php.net/mongo).

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
It will only work for **PHP 5.3+ projects** which use a **structure matching [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)**.
Furthermore, [composer](http://getcomposer.org/) usage is strongly encouraged.

Installation
------------

### Composer

First, in your composer.json, add the requirement line for Boomgo.

```javascript
    "require": {
        "retentio/boomgo": "dev-master"
    }
```

Then get composer and run the install command.

```bash
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

Usage
-----

At the moment Boomgo support only annotation definition. Yet it uses only a single tag: by default "@Persistent" (you can change it).
To persist some attributes of your model, Boomgo needs 3 things :

1. A dedicated & unique namespace part for your persistent classes (default "Document").
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

    public function $getMyField()
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

Boomgo will generate (default: aside of your document folder) a mapper class called `VendorName\Project\Mapper\MyPersistedClassMapper`.
The mapper expose 3 methods:

* `->serialize($yourObject)`
* `->unserialize($yourArray)`
* '->hydrate($yourObject, $yourArray)'

Then, the usage become really simple:

```php
<?php

// Create your connection with the native mongoDB php driver
$mongo = new \Mongo("mongodb://127.0.0.1:27017");

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

Boomgo handles **native PHP Mongo types** (MongoId, etc.), **embedded document** and **nested collection**.
Since, Boomgo love simple & efficient things, so annotation are not used for that. Instead it rely on... docblock with the famous under-used @var tag.

```php
<?php

namespace VendorName\Project\Document;

class DocumentClass
{
    /**
     * @Persistent
     * @var \MongoId
     */
    private $id

    /**
     * @Persistent
     * @var string
     */
    private $myField

    /**
     * @Persistent
     * @var VendorName\Project\EmbeddedDocument
     */
    private $embeddedDocument // a single embedded document

    /**
     * @Persistent
     * @var array [VendorName\Project\EmbeddedDocument]
     */
    private $embeddedCollection // many embedded document the [ ] chars are mandatory

    // getters & setters
}

?>
```

Limitations
-----------

* It doesn't & won't manage object relation for a simple reason: [MongoDB is a NON-RELATIONAL storage](http://www.mongodb.org/display/DOCS/Database+References).
* It doesn't & won't provide [identity map](http://en.wikipedia.org/wiki/Identity_map) etc.
* It doesn't & won't make coffee.

If you're looking for full-featured php ODM, you should look at [Mandango](https://github.com/mandango/mandango) which use active record/class generator implementation, and also [Doctrine MongoDB ODM](http://www.doctrine-project.org/projects/mongodb_odm/current/docs/en) data-mapping implementation.

Roadmap
-------

Add functional tests, more parsers (yml, xml and json), ActiveRecord implementation. Feel free to contribute !


How to run unit tests
---------------------

Boomgo is unit tested with [atoum](https://github.com/mageekguy/atoum), the dependency is not shipped by default in Boomgo, with composer you have to run the command
```bash
$ php composer.phar update --install-suggests
```

To run the complete test suite, open a shell and type :

``` bash
$ cd path/to/Boomgo
$ php vendor/bin/atoum -c .atoum.php -d tests
```

Want to test on a single class while contributing ? Here is an example with _AnnotationParser_ class :

``` bash
$ php vendor/bin/atoum -c .atoum.php -f tests/units/Parser/AnnotationParser.php
```
