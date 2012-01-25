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

    public function testNormalize()
    {
        $mapper = new Boomgo\Mapper();

        // Should not alter scalar and null value
        $output = $mapper->normalize(1);
        $this->assert
            ->variable($output)
            ->isEqualTo(1);

        $output = $mapper->normalize(null);
        $this->assert
            ->variable($output)
            ->isNull();

        // Should return an empty array when providing an empty object
        $output = $mapper->normalize(new Mock\Document());
        $this->assert
            ->array($output)
            ->isEmpty();

        // Should return the exact same array when providing an array
        $output = $mapper->normalize(array('yet' => 'another', 'array', 17 => 13));
        $this->assert
            ->array($output)
            ->isIdenticalTo(array('yet' => 'another', 'array', 17 => 13));

        // Should throw an exception when dealing with non-normalizable value
        $file = fopen(__FILE__, 'r');
        $this->assert
            ->exception(function() use ($mapper, $file) {
                    $mapper->normalize($mapper->normalize($file));
                })
            ->isInstanceOf('RuntimeException');

        fclose($file);
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
            ->hasKeys(array('_id', 
                'mongo_string',
                'mongo_number',
                'mongo_document',
                'mongo_array'))
            ->notHasKey('attribute');

        // Should recursively normalize single embedded object
        $embed = new Mock\EmbedDocument();
        $embed->setMongoString('a embed stored string');
        $embed->setAttribute('an embed excluded value');
        
        $document = new Mock\Document();
        $document->setMongoDocument($embed);
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongo_document');
            
        $this->assert
            ->array($array['mongo_document'])
            ->hasKeys(array('_id', 'mongo_string', 'mongo_number'));

        // Should recursively include array
        $document = new Mock\Document();
        $document->setMongoArray(array('an' => 'embedded', 'array', 6 => 2));
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongo_array');
            
        $this->assert
            ->array($array['mongo_array'])
            ->isIdenticalTo(array('an' => 'embedded', 'array', 6 => 2));
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
        $camelCase = $mapper->camelize('hello_world_pol');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('HelloWorldPol');

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
        $underscore = $mapper->uncamelize('HelloWorldPol');
        $this->assert
            ->string($underscore)
            ->isEqualTo('hello_world_pol');

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

    /**
     * An single embedded document 
     * @Mongo
     */
    private $mongoDocument;

    /**
     * An embedded array 
     * @Mongo
     */
    private $mongoArray;


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

    public function setMongoDocument($value) 
    {
        $this->mongoDocument = $value;
    }

    public function getMongoDocument() 
    {
        return $this->mongoDocument;
    }
    public function setMongoArray($value)
    {
        $this->mongoArray = $value;
    }
    public function getMongoArray()
    {
        return $this->mongoArray;
    }
}

class EmbedDocument
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

    /**
     * A pure php attribute
     * non persisted into mongo
     */
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