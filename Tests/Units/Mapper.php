<?php

namespace Boomgo\tests\units;

require_once __DIR__.'/../../vendor/mageekguy.atoum.phar';
include __DIR__.'/../../Mapper.php';

use Boomgo;

class Mapper extends \mageekguy\atoum\test
{
    public function documentProvider()
    {
        
    }

    public function testToArray()
    {
        $mapper = new Boomgo\Mapper();

        // Should throw exception if argument is not an object
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->toArray(1);;
                })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an object');
        
        // Should throw exception if object don't have/expose identifer (id, getId)
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->toArray(new \stdClass());
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Invalid identifier prerequisite');

        // Should return an empty array when providing an empty object
        $document = new Mock\Document();
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();
        
        // Should return an empty array when all mongo keys are null
        $document = new Mock\Document();
        $document->setAttribute('an excluded value');
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an array filled with every mongo keys if at least one mongo key is filled.
        $document = new Mock\Document();
        $document->setMongoString('a stored string');
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKeys(array('_id', 'mongo_string', 'mongo_number'));
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Boomgo\Mapper();

        // Should throw exception if array has no _id
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->hydrate('\\stdClass',array());
                })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Data without _id are not yet supported');
        
        // Sould throw exception if constructor has mandatory prerequesite
        $this->assert
            ->exception(function() use ($mapper,$ns) {
                    $mapper->hydrate($ns.'DocummentConstructRequired', array('_id' => 1));
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Unable to hydrate object requiring constructor param');

        // Should throw exception if object don't have/expose identifer (id, setId)
        $this->assert
            ->exception(function() use ($mapper, $ns) {
                    $mapper->hydrate($ns.'DocummentConstruct', array('_id' => 1));
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Invalid identifier prerequisite');

        // Should hydrate correctly an object
        $array = array('_id' => 'identifier',
            'mongo_string' => 'a mongo string',
            'mongo_number' => 1664,
            'attribute' => 'a parasite attribute'); 

        $object = $mapper->hydrate($ns.'Document', $array);

        $this->assert
            ->object($object)
            ->isInstanceOf('Boomgo\tests\units\Mock\Document');

        $this->assert
            ->variable($object->getId())
            ->isEqualTo('identifier');

        $this->assert
            ->variable($object->getMongoString())
            ->isEqualTo('a mongo string');

        $this->assert
            ->variable($object->getMongoNumber())
            ->isEqualTo(1664);

        $this->assert
            ->variable($object->getAttribute())
            ->isNull();
    }

    public function testCamelize()
    {
        $mapper = new Boomgo\Mapper();

        // Should camelize an underscored string
        $camelCase = $mapper->camelize('hello_world');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('HelloWorld');

        // Should handle prefixed or suffixed string with underscore
        $camelCase = $mapper->camelize('_world_');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('World');

        // Should handle double underscored string
        $camelCase = $mapper->camelize('hello__world_');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('HelloWorld');
    }

    public function testUncamelize()
    {
        $mapper = new Boomgo\Mapper();

        // Should underscore a CamelCase string
        $underscore = $mapper->uncamelize('HelloWorld');
        $this->assert
            ->string($underscore)
            ->isEqualTo('hello_world');

        // Should also manage lower camelCase
        $underscore = $mapper->uncamelize('helloWorld');
        $this->assert
            ->string($underscore)
            ->isEqualTo('hello_world');
    }
}

namespace Boomgo\tests\units\Mock;

class Document
{
    /**
     * Identifier
     * @Mongo
     */
    private $id;

    /**
     * A mongo stored string
     * @Mongo
     */
    private $mongoString;

    /**
     * A mongo number
     * @Mongo
     */
    private $mongoNumber;

    private $attribute;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id =$id;
    }

    public function setMongoString($value)
    {
        $this->mongoString = $value;
    }
      
    public function getMongoString()
    {
        return $this->mongoString;
    }        

    public function setMongoNumber($value)
    {
        $this->mongoNumber = $value;
    }

    public function getMongoNumber()
    {
        return $this->mongoNumber;
    }

    public function setAttribute($value)
    {
        $this->attribute = $value;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
}

class DocummentConstruct
{
    public function __construct($options = array()) {}
}

class DocummentConstructRequired
{
    public function __construct($options) {}
}