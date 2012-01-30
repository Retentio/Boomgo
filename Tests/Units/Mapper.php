<?php

namespace Boomgo\tests\units;

use Boomgo;
use Boomgo\Parser;

require_once __DIR__.'/../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../Mapper.php';
include __DIR__.'/../../Mapper/Map.php';

include __DIR__.'/../../Parser/ParserInterface.php';
include __DIR__.'/../../Parser/AnnotationParser.php';

include __DIR__.'/../../Formatter/FormatterInterface.php';

include __DIR__.'/Mock/Document.php';
include __DIR__.'/Mock/Formatter.php';

class Mapper extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(new Mock\Formatter()),new Mock\Formatter());
    }
/*
    public function testNormalize()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());

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
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());

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
*/
    public function testToArray()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(new Mock\Formatter()));

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

        // Should return an complete array
        $embedDocument = new Mock\EmbedDocument();
        $embedDocument->setMongoString('an embed string');
        $embedDocument->setMongoNumber(2);
        $embedDocument->setMongoArray(array('an' => 'embed array', 7 => 2));

        $embedCollection = array();
        for ($i = 0; $i < 3; $i ++) {
            $embedCollection[] = $embedDocument;
        }

        $document = new Mock\Document();
        $document->setId('an identifier');
        $document->setMongoString('a string');
        $document->setMongoNumber(1);
        $document->setMongoDocument($embedDocument);
        $document->setMongoCollection($embedCollection);
        $document->setMongoArray(array('an' => 'array', 8 => 1));

        $array = $mapper->toArray($document);

        $embedData = array('mongoString' => 'an embed string', 
            'mongoNumber' => 2,
            'mongoArray' => array('an' => 'embed array', 7 => 2));

        $embedCollectionData = array();
        for ($i = 0; $i < 3; $i ++) {
            $embedCollectionData[] = $embedData;
        }

        $expectedArray = array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoNumber' => 1,
            'mongoDocument' => $embedData,
            'mongoCollection' => $embedCollectionData,
            'mongoArray' => array('an' => 'array', 8 => 1));

        $this->assert
            ->array($array)
            ->hasSize(6)
            ->isIdenticalTo($expectedArray);
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(new Mock\Formatter()));

        $embedData = array('mongoString' => 'an embed string', 
            'mongoNumber' => 2,
            'mongoArray' => array('an' => 'embed array', 7 => 2));

        $embedCollectionData = array();
        for ($i = 0; $i < 3; $i ++) {
            $embedCollectionData[] = $embedData;
        }

        $data =  array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoNumber' => 1,
            'mongoDocument' => $embedData,
            'mongoCollection' => $embedCollectionData,
            'mongoArray' => array('an' => 'array', 8 => 1));

        $object = $mapper->hydrate($ns.'Document', $data);

        $this->assert
            ->object($object)
            ->isInstanceOf($ns.'Document');

        $this->assert
            ->string($object->getId())
            ->isEqualTo('an identifier');
        
        $this->assert
            ->string($object->getMongoString())
            ->isEqualTo('a string');

        $this->assert
            ->integer($object->getMongoNumber())
            ->isEqualTo(1);

        $embedObject = $object->getMongoDocument();
        $this->assert
            ->object($embedObject)
            ->isInstanceOf($ns.'EmbedDocument');

        $this->assert
            ->string($embedObject->getMongoString())
            ->isEqualTo('an embed string');

        $this->assert
            ->integer($embedObject->getMongoNumber())
            ->isEqualTo(2);

        $this->assert
            ->array($embedObject->getMongoArray())
            ->isEqualTo(array('an' => 'embed array', 7 => 2));

        $embedCollection = $object->getMongoCollection();
        $this->assert
            ->array($embedCollection)
            ->hasSize(3);
        
        $embedObjectCollected = $embedCollection[0];
        $this->assert
            ->object($embedObjectCollected)
            ->isInstanceOf($ns.'EmbedDocument');
    }
        
/*
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

           }
*/
}