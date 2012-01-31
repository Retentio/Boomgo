<?php

namespace Boomgo\tests\units\Mapper;

use Boomgo\Cache;
use Boomgo\Mapper;
use Boomgo\Parser;

use Boomgo\tests\units\Mock;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Mapper/DataMapper.php';
include __DIR__.'/../../../Mapper/Map.php';

include __DIR__.'/../../../Cache/CacheInterface.php';

include __DIR__.'/../../../Parser/ParserInterface.php';
include __DIR__.'/../../../Parser/ParserProvider.php';
include __DIR__.'/../../../Parser/AnnotationParser.php';

include __DIR__.'/../../../Formatter/FormatterInterface.php';

include __DIR__.'/../Mock/Cache.php';
include __DIR__.'/../Mock/Document.php';
include __DIR__.'/../Mock/Formatter.php';

class DataMapper extends \mageekguy\atoum\test
{
    public function documentProvider()
    {
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

        return $document;
    }

    public function arrayProvider()
    {
        $embedArray = array('mongoString' => 'an embed string', 
            'mongoNumber' => 2,
            'mongoArray' => array('an' => 'embed array', 7 => 2));

        $embedCollectionArray = array();
        for ($i = 0; $i < 3; $i ++) {
            $embedCollectionArray[] = $embedArray;
        }

        $array =  array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoNumber' => 1,
            'mongoDocument' => $embedArray,
            'mongoCollection' => $embedCollectionArray,
            'mongoArray' => array('an' => 'array', 8 => 1));
        
        return $array;
    }

    public function test__construct()
    {
        $mapper = new Mapper\DataMapper(new Parser\AnnotationParser(new Mock\Formatter(), new Mock\Cache()));
    }
/*
    public function testToArray()
    {
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
        $mapper = new Mapper\DataMapper(new Parser\AnnotationParser(new Mock\Formatter(), new Mock\Cache()));

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
        $document = $this->documentProvider();
        $array = $mapper->toArray($document);

        $expectedArray = $this->arrayProvider();

        $this->assert
            ->array($array)
            ->hasSize(6)
            ->isIdenticalTo($expectedArray);
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Mapper\DataMapper(new Parser\AnnotationParser(new Mock\Formatter(), new Mock\Cache()));

        $array = $this->arrayProvider();
        
        $object = $mapper->hydrate($ns.'Document', $array);

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
*/
}