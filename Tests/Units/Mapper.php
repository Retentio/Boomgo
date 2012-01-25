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
        
        // Should return an empty array when providing an empty object
        $array = $mapper->toArray(new \stdClass()); 
        
        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an empty array when providing object without value
        $document = new Mock\Document();
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an array with data defined mongo
        $document = new Mock\EmbedDocument();
        $document->setMongoString('a string');
        $document->setMongoNumber(5);

        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->isIdenticalTo(array('mongo_string' => 'a string',
                'mongo_number' => 5));

        // Should return an array with normalized key _id when object has identifier
        $document = new Mock\Document();
        $document->setId('an id');
        $document->setMongoString('a string');
        $document->setMongoNumber(5);

        $array = $mapper->toArray($document);
        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('_id')
            ->notHasKey('id');

        $this->assert
            ->variable($array['_id'])
            ->isIdenticalTo('an id');

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
            ->hasKeys(array('mongo_string', 'mongo_number'))
            ->strictlyContainsValues(array('a embed stored string'));

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

        // Should recursively normalize embedded collection
        $embedCollection = array();
        for ($i = 0; $i < 10; $i ++) {
            $embedDocument = new Mock\EmbedDocument();
            $embedDocument->setMongoString('a embed stored string');
            $embedDocument->setAttribute('an embed excluded value');
            $embedCollection[] = $embedDocument;
        }

        $document = new Mock\Document();
        $document->setMongoCollection($embedCollection);
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongo_collection');

        $this->assert
            ->array($array['mongo_collection'])
            ->isNotEmpty()
            ->hasSize(10);

        for ($i = 0; $i < 10; $i ++) {
            $this->assert
                ->array($array['mongo_collection'][$i])
                ->isNotEmpty()
                ->hasKeys(array('mongo_string', 'mongo_number'))
                ->strictlyContainsValues(array('a embed stored string'));
        }
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Boomgo\Mapper();
        
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
            ->hasMessage('Object do not handle identifier');

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

    public function testHasValidIdentifier()
    {
        $mapper = new Boomgo\Mapper();

        // Should return true when object provide a valid identifier implementation
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should return false when an object provide an uncomplete identifier implementation (getter)
        $object = new Mock\DocumentMissGetter();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should return false when an object provide an uncomplete identifier implementation (setter)
        $object = new Mock\DocumentMissSetter();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

         // Should return false when an object provide a valid identifier implementation without mongo annotation
        $object = new Mock\DocumentExcludedId();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should throw exception when an bject provide a complete yet invalid identifier implem (getter)
        $object = new Mock\DocumentInvalidGetter();
        $reflection = new \ReflectionObject($object);

        $this->assert
            ->exception(function() use ($mapper, $reflection) {
                    $mapper->hasValidIdentifier($reflection);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Object expect an id but do not expose valid accessor/mutator');

        // Should throw exception when an object provide a complete yet invalid identifier implem (setter)
        $object = new Mock\DocumentInvalidSetter();
        $reflection = new \ReflectionObject($object);

        $this->assert
            ->exception(function() use ($mapper, $reflection) {
                    $mapper->hasValidIdentifier($reflection);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Object expect an id but do not expose valid accessor/mutator'); 
    }

    public function TestIsValidAccessor()
    {
        // Should return true when getter is public and do not required argument
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidAccessor($reflection->getMethod('getId'));

        $this->assert
            ->boolean($bool)
            ->isTrue();
        
        // Should return false when getter is invalid
        $object = new Mock\DocumentInvalidGetter();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidAccessor($reflection->getMethod('getId'));

        $this->assert
            ->boolean($bool)
            ->isFalse();
    }

    public function TestIsValidMutator()
    {
        // Should return true when setter is public and require only one argument
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidAccessor($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isTrue();
        
        // Should return false when setter is invalid
        $object = new Mock\DocumentInvalidSetter();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidAccessor($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isFalse();
    }
}

namespace Boomgo\tests\units\Mock;

/**
 * A valid Boomgo document class
 * fully exposing mapper capabilities with identifier
 */
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
     * A embedded collection 
     * @Mongo
     */
    private $mongoCollection;

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

    public function setMongoCollection($value)
    {
        $this->mongoCollection = $value;
    }
    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }    
}

/**
 * A valid Boomgo document class
 * exposing mapper capabilities without identifier
 * (embed document or capped collection)
 */
class EmbedDocument
{
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

/**
 * A invalid Boomgo document class
 * using identifier with a missing mutator (setId)
 */
class DocumentMissSetter
{
    /**
     * Identifier
     * @Mongo
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with a missing accessor (getId)
 */
class DocumentMissGetter
{
    /**
     * Identifier
     * @Mongo
     */
    private $id;

    public function setId($id)
    {
        return $this->id;
    }
}

/**
 * A valid Boomgo document class
 * Appear using identifier yet do not defined @Mongo
 * (the document class must not use mongo identifier)
 */
class DocumentExcludedId
{
    /**
     * Identifier private, non persisted
     */
    private $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with an invalid mutator (setId)
 */
class DocumentInvalidSetter
{
    /**
     * Identifier private, non persisted
     * @Mongo
     */
    private $id;

    public function setId()
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with an invalid accessor (getId)
 */
class DocumentInvalidGetter
{
    /**
     * Identifier private, non persisted
     * @Mongo
     */
    private $id;

    public function setId()
    {
        $this->id = $id;
    }

    public function getId($id)
    {
        return $this->id;
    }
}

/**
 * A valid Boomgo document class
 * with a constructor using optionnal param
 */
class DocummentConstruct
{
    public function __construct($options = array()) {}
}

/**
 * A invalid Boomgo document class
 * with a constructor using mandatory param
 */
class DocummentConstructRequired
{
    public function __construct($options) {}
}