<?php

namespace Boomgo\tests\units;

use Boomgo;

require_once __DIR__.'/../../vendor/mageekguy.atoum.phar';
include __DIR__.'/../../Mapper.php';
include __DIR__.'/Mock/Document.php';

class Mapper extends \mageekguy\atoum\test
{
    public function documentProvider()
    {
        
    }

    public function test__construct()
    {
        $mapper = new Boomgo\Mapper('@MyHypeAnnot');

        $this->assert
            ->string($mapper->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');
    }

    public function testSetGetAnnotation()
    {
        $mapper = new Boomgo\Mapper();

        // Should set and get annotation
        $mapper->setAnnotation('@MyHypeAnnot');

        $this->assert
            ->string($mapper->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');
        
        // Should throw exception on invalid annotation
        $this->assert
            ->exception(function() use ($mapper) {
                $mapper->setAnnotation('invalid');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Annotation should start with @ char');

        $this->assert
            ->exception(function() use ($mapper) {
                $mapper->setAnnotation('@12');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Annotation should start with @ char');
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
                'mongo_number' => 5,
                'mongo_array' => null));

        

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
            $embedDocument = new Mock\Document();
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

        // Should hydrate correctly a document containing multiple levels of nested documents, collections and arrays
        // This test emulate a complexe document embedding
        // {
        //      _id:
        //      mongo_string: 
        //      mongo_document:
        //      mongo_collection: 10 x Mock\Document which embed 3 x Mock\EmbedDocument (and mutliples arrays / assoc arrays);
        //      mongo_collection_embed: 3 x Mock\EmbedDocument (and 1 array nested in 1 assoc array)
        //      mongo_array: 1 assoc array which embed 1 array
        // }
        $array = array('yet', 'another', 'embed', 'array'); 

        $assocArray = array('an' => 'associative', 8 => 'array', 'containing' => $array);
        
        $collectionEmbedDocument = array();
        for ($j = 0; $j < 3; $j ++) {
            $document = array('mongo_string' => 'a EMBED DOCUMENT collection-embed stored string',
                'mongo_number' => null,
                'mongo_array' => $assocArray);
            
            $collectionEmbedDocument[] = $document;
        } 
        
        $collectionDocument = array();
        for ($i = 0; $i < 10; $i ++) {
            $document = array('_id' => 'identifier'.$i,
                'mongo_string' => 'a DOCUMENT collection-embed stored string',
                'mongo_number' => null,
                'mongo_document' => null,
                'mongo_collection' => null,
                'mongo_collection_embed' => $collectionEmbedDocument,
                'mongo_array' => $assocArray);
            $collectionDocument[] = $document;
        }
        
        $document = array('mongo_string' => 'embed mongo string',
            'mongo_number' => 1337,
            'mongo_array' => $assocArray); 
        
        $data = array('_id' => 'identifier',
            'mongo_string' => 'a mongo string',
            'mongo_number' => 1664,
            'mongo_document' => $document,
            'mongo_collection' => $collectionDocument,
            'mongo_collection_embed' => $collectionEmbedDocument,
            'mongo_array' => $array);

        $object = $mapper->hydrate($ns.'Document', $data);

        $this->assert
            ->object($object)
            ->isInstanceOf('Boomgo\tests\units\Mock\Document');

        $this->assert
            ->string($object->getMongoString())
            ->isEqualTo('a mongo string');

        $this->assert
            ->integer($object->getMongoNumber())
            ->isEqualTo(1664);
        
        // check single embedded document (1 x Mock\Document)
        $embedObject = $object->getMongoDocument();

        $this->assert
            ->object($embedObject)
            ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

        $this->assert
            ->string($embedObject->getMongoString())
            ->isEqualTo('embed mongo string');

        $this->assert
            ->integer($embedObject->getMongoNumber())
            ->isEqualTo(1337);
        
        // Check the first embedded collection level (10 x Mock\Document)
        $embedCollection = $object->getMongoCollection();

        $this->assert
            ->array($embedCollection)
            ->isNotEmpty()
            ->hasSize(10);
        
        foreach($embedCollection as $embedDocument) {
            $this->assert
                ->object($embedDocument)
                ->isInstanceOf('Boomgo\tests\units\Mock\Document');

            $this->assert
                ->string($embedDocument->getMongoString())
                ->isEqualTo('a DOCUMENT collection-embed stored string');

            $this->assert
                ->variable($embedDocument->getAttribute())
                ->isNull();

            $embedArray = $embedDocument->getMongoArray();
            $this->assert
                    ->array($embedArray)
                    ->hasSize(3)
                    ->isIdenticalTo($assocArray);

            $this->assert
                    ->array($embedArray['containing'])
                    ->hasSize(4)
                    ->isIdenticalTo($array);

            // check the second nested collection level (3 x Mock\EmbedDocument)
            $nestedEmbedCollection = $embedDocument->getMongoCollectionEmbed();
            $this->assert
                ->array($nestedEmbedCollection)
                ->isNotEmpty()
                ->hasSize(3);

            foreach($nestedEmbedCollection as $nestedEmbedDocument) {
                $this->assert
                    ->object($nestedEmbedDocument)
                    ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

                $this->assert
                    ->string($nestedEmbedDocument->getMongoString())
                    ->isEqualTo('a EMBED DOCUMENT collection-embed stored string');

                $this->assert
                    ->variable($nestedEmbedDocument->getAttribute())
                    ->isNull();
                
                $nestedAssocArray = $nestedEmbedDocument->getMongoArray();
                $this->assert
                    ->array($nestedAssocArray)
                    ->hasSize(3)
                    ->isIdenticalTo($assocArray);

                $this->assert
                    ->array($nestedAssocArray['containing'])
                    ->hasSize(4)
                    ->isIdenticalTo($array);
            }
        }

        // Finally check the embedded collection of EmbedDocument at the root document (3 x Mock\EmbedDocument)
        $embedCollectionEmbed = $object->getMongoCollectionEmbed();
        $this->assert
                ->array($embedCollectionEmbed)
                ->isNotEmpty()
                ->hasSize(3);

        foreach ($embedCollectionEmbed as $embedDocumentEmbed)
        {
            $this->assert
                    ->object($embedDocumentEmbed)
                    ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

            $this->assert
                ->string($embedDocumentEmbed->getMongoString())
                ->isEqualTo('a EMBED DOCUMENT collection-embed stored string');

            $this->assert
                ->variable($embedDocumentEmbed->getAttribute())
                ->isNull();
            
            $embedAssocArray = $embedDocumentEmbed->getMongoArray();
            $this->assert
                ->array($embedAssocArray)
                ->hasSize(3)
                ->isIdenticalTo($assocArray);

            $this->assert
                ->array($embedAssocArray['containing'])
                ->hasSize(4)
                ->isIdenticalTo($array);
        }

        // Ultimate non-so-unit test to check consistency between hydrate & toArray 
        $reverse = $mapper->toArray($object);
        $this->assert
            ->array($reverse)
            ->isIdenticalTo($data);
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

    public function testIsValidAccessor()
    {
        $mapper = new Boomgo\Mapper();

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

    public function testIsValidMutator()
    {
        $mapper = new Boomgo\Mapper();

        // Should return true when setter is public and require only one argument
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidMutator($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isTrue();
        
        // Should return false when setter is invalid
        $object = new Mock\DocumentInvalidSetter();
        $reflection = new \ReflectionObject($object);
        $bool = $mapper->isValidMutator($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isFalse();
    }

    public function testIsBoomgoProperty()
    {
        $mapper = new Boomgo\Mapper();

         // Should return false if proprerty don't have annotation
        $document = new Mock\DocumentExcludedId();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $bool = $mapper->isBoomgoProperty($reflectedProperty);
        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should return true if proprerty has annotation
        $document = new Mock\Document();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $bool = $mapper->isBoomgoProperty($reflectedProperty);
        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should throws exception if property has 2 inline annotations
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('inline');
        $this->assert
            ->exception(function() use ($mapper, $reflectedProperty) {
                    $mapper->isBoomgoProperty($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Boomgo annotation should occur only once');

        // Should throws exception if property has 2 multi-line annotations
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('multiline');
        $this->assert
            ->exception(function() use ($mapper, $reflectedProperty) {
                    $mapper->isBoomgoProperty($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Boomgo annotation should occur only once');
    }

    public function testParseMetadata()
    {
        $mapper = new Boomgo\Mapper();

        // Should throw exception if annotation is missing
        $document = new Mock\DocumentExcludedId();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $this->assert
            ->exception(function() use ($mapper, $reflectedProperty) {
                    $mapper->parseMetadata($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Malformed metadata');

        // Should throw exception if annotation is incomplete
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('incomplete');

        $this->assert
            ->exception(function() use ($mapper, $reflectedProperty) {
                    $mapper->parseMetadata($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Malformed metadata');

        // Should return an array of metadata
        $document = new Mock\Document();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('mongoDocument');

        $metadata = $mapper->parseMetadata($reflectedProperty);
        $this->assert
            ->array($metadata)
            ->isNotEmpty()
            ->hasSize(2)
            ->strictlyContainsValues(array('Document', 'Boomgo\tests\units\Mock\EmbedDocument'));
    }
}