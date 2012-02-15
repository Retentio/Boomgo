Boomgo, a micro PHP ODM for [MongoDB](http://www.mongodb.org/)
==============================================================

_Boomgo still a work in progress and is initially developped for [Retentio](http://retent.io)._

Boomgo is a **light** and **simple** Object Document Mapper on top of the [MongoDB php native driver](http://php.net/mongo).

Philosophy
-------------

Boomgo ODM focuses on the mapping process between PHP objects and MongoDB Documents, It doesn't abstract any feature provided by the native php driver. This way, Boomgo allows you to **keep the full control about your MongoDB interactions** (querying, map reduce, ...).

_In short, Boomgo offers a handy way to manipulate your MongoDB Documents with PHP Objects._

Features
--------

* Build, cache & use a strict Map definition for your object.
* Hydrate PHP Object from a MongoDB results set.
* Normalize PHP Object to mongo-storable array.
* Handle hydration process of embedded document / collection.
* Provide live hydration with schemaless style

Limitations
-----------

* It doesn't & won't manage object relation for a simple reason: [MongoDB is a NON-RELATIONAL storage](http://www.mongodb.org/display/DOCS/Database+References).
* It doesn't & won't provide [identity map](http://en.wikipedia.org/wiki/Identity_map) etc.
* It doesn't & won't make coffee.

If you're looking for full-featured php ODM, you should look at [Mandango](https://github.com/mandango/mandango) which use active record/class generator implementation, and also [Doctrine MongoDB ODM](http://www.doctrine-project.org/projects/mongodb_odm/current/docs/en) data-mapping implementation.

Roadmap
-------

Improve unit tests (refacto and upgrade coverage), Add a CLI cache warmer, more formatters, more parsers (yml, xml and json). Feel free to contribute !

Example
-------

Suppose this class (and the embed classes) :

```php
<?php

class MyDocumentClass
{

    /**
     * A custom field not persisted (no annotation)
     */
    private $myCustomField

    /**
     * A persisted field
     *
     * @Boomgo
     */
    private $myField

    /**
     * A persisted embed document
     *
     * @Boomgo Document My\Namespace\MyEmbedDocument
     */
    private $myEmbedDocument

    /**
     * A persisted embed collection of document
     * it should be an array of the same document type
     * Boomgo do not support dynamic mapping
     *
     * @Boomgo Collection My\Other\Namespace\MyOtherEmbedDocument
     */
    private $myEmbedCollection

    // Some getters/setters to allow acces to the private properties

    public function getMyField()
    {
        return $this->myField;
    }

    public function setMyField($value)
    {
        $this->myField = $value
    }

    public function getMyEmbedDocument()
    {
        return $this->myEmbedDocument;
    }

    public function setMyEmbedDocument(My\Namespace\MyEmbedDocument $embedDocument)
    {
        $this->myEmbedDocument = $embedDocument;
    }

    public function getMyEmbedCollection()
    {
        return $this->myEmbedCollection;
    }

    public function setMyEmbedCollection(array $embedCollection)
    {
        $this->myEmbedCollection = $embedCollection;
    }
}
?>
```

Boomgo allows you to store it like this :

```php
<?php

// Create your connection with the native mongoDB php driver
$mongo = new \Mongo("mongodb://127.0.0.1:27017");

// Create your object as your used to
$myObject = new MyDocumentClass()

// ... do some stuff with the object ...

// The map definition for a PHP class will be builded only once, then cached to the disk.
$cache = new FileCache('my/custom/path/to/cache/the/map-definition');

// This formatter will convert CamelCase php attribute to lower underscore mongo key
$formatter = new Underscore2CamelFormatter();

// This parser will be responsible to build the map definition using annotation @Boomgo
$parser = new AnnotationParser($formatter);

// This mapper will build the map once, then reuse cached map
$mapper = new StrictMapper($parser, $cache);

$mongoableArray = $mapper->toArray($myObject);

// Save with the native php driver
$mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->save($mongoableArray);
?>
```

Of course, Boomgo is able to hydrate an object from a mongo result :

```php
<?php

$result = $mongo->selectDB('my_db')
    ->selectCollection('my_collection')
    ->findOne(array('my_field' => 'my value'));

$object = $mapper->hydrate('My\Namsespace\MyDocumentClass', $result);

// or

$object = new MyDocumentClass();
$object = $mapper->hydrate($object, $result);
?>
```

Boomgo handles hydration of embedded document, so with our previous example, we could do this :

```php
<?php

$object->getMyEmbedDocument() // hydrated instance of My\Namespace\MyEmbedDocument
?>
```

How to run unit tests
---------------------

Boomgo is unit tested with [atoum](https://github.com/mageekguy/atoum) : atoum is distributed with a phar archive and is bundled by default within Boomgo.

To run the complete test suite, open a shell and type :

``` bash
$ cd path/to/Boomgo
$ php vendor/mageekguy/atoum/bin/atoum -c .atoum.php -d src/Boomgo/tests
```

Want to test on a single class while contributing ? Here is an example with _AnnotationParser_ class :

``` bash
$ php vendor/mageekguy/atoum/bin/atoum -c .atoum.php -f tests/units/Parser/AnnotationParser.php
```
