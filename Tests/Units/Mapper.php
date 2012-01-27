<?php

namespace Boomgo\tests\units;

use Boomgo;

require_once __DIR__.'/../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../Mapper.php';
include __DIR__.'/../../Formatter/FormatterInterface.php';

include __DIR__.'/Mock/Document.php';
include __DIR__.'/Mock/Formatter.php';

class Mapper extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        $mapper = new Boomgo\Mapper(new Mock\Formatter(),'@MyHypeAnnot');

        $this->assert
            ->string($mapper->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');
    }

    public function testSetGetAnnotation()
    {
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
            ->isIdenticalTo(array('mongoString' => 'a string',
                'mongoNumber' => 5,
                'mongoArray' => null));

        

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
                'mongoString',
                'mongoNumber',
                'mongoDocument',
                'mongoArray'))
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
            ->hasKey('mongoDocument');
            
        $this->assert
            ->array($array['mongoDocument'])
            ->hasKeys(array('mongoString', 'mongoNumber'))
            ->strictlyContainsValues(array('a embed stored string'));

        // Should recursively include array
        $document = new Mock\Document();
        $document->setMongoArray(array('an' => 'embedded', 'array', 6 => 2));
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongoArray');
            
        $this->assert
            ->array($array['mongoArray'])
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
        $document->setmongoCollection($embedCollection);
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongoCollection');

        $this->assert
            ->array($array['mongoCollection'])
            ->isNotEmpty()
            ->hasSize(10);

        for ($i = 0; $i < 10; $i ++) {
            $this->assert
                ->array($array['mongoCollection'][$i])
                ->isNotEmpty()
                ->hasKeys(array('mongoString', 'mongoNumber'))
                ->strictlyContainsValues(array('a embed stored string'));
        }
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Boomgo\Mapper(new Mock\Formatter());
        
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
            'mongoString' => 'a mongo string',
            'mongoNumber' => 1664,
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
        //      mongoString: 
        //      mongoDocument:
        //      mongoCollection: 10 x Mock\Document which embed 3 x Mock\EmbedDocument (and mutliples arrays / assoc arrays);
        //      mongoCollectionEmbed: 3 x Mock\EmbedDocument (and 1 array nested in 1 assoc array)
        //      mongoArray: 1 assoc array which embed 1 array
        // }
        $array = array('yet', 'another', 'embed', 'array'); 

        $assocArray = array('an' => 'associative', 8 => 'array', 'containing' => $array);
        
        $collectionEmbedDocument = array();
        for ($j = 0; $j < 3; $j ++) {
            $document = array('mongoString' => 'a EMBED DOCUMENT collection-embed stored string',
                'mongoNumber' => null,
                'mongoArray' => $assocArray);
            
            $collectionEmbedDocument[] = $document;
        } 
        
        $collectionDocument = array();
        for ($i = 0; $i < 10; $i ++) {
            $document = array('_id' => 'identifier'.$i,
                'mongoString' => 'a DOCUMENT collection-embed stored string',
                'mongoNumber' => null,
                'mongoDocument' => null,
                'mongoCollection' => null,
                'mongoCollectionEmbed' => $collectionEmbedDocument,
                'mongoArray' => $assocArray);
            $collectionDocument[] = $document;
        }
        
        $embedDocument = array('mongoString' => 'embed mongo string',
            'mongoNumber' => 1337,
            'mongoArray' => $assocArray); 
        
        $data = array('_id' => 'identifier',
            'mongoString' => 'a mongo string',
            'mongoNumber' => 1664,
            'mongoDocument' => $embedDocument,
            'mongoCollection' => $collectionDocument,
            'mongoCollectionEmbed' => $collectionEmbedDocument,
            'mongoArray' => $array);

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
        $embedCollection = $object->getmongoCollection();

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
            $nestedEmbedCollection = $embedDocument->getmongoCollectionEmbed();
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
        $embedCollectionEmbed = $object->getmongoCollectionEmbed();
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

    public function testHasValidIdentifier()
    {
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Mock\Formatter());

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