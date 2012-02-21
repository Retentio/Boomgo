<?php

/**
 * This file is part of the Boomgo PHP ODM.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\tests\units\Mapper;

use Boomgo\Mapper;
use Boomgo\Cache\CacheInterface;
use Boomgo\Parser\ParserInterface;

use Boomgo\tests\units\Mock;

/**
 * StrictMapper tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class StrictMapper extends \mageekguy\atoum\test
{
    public function mapProvider()
    {
        $mapEmbedDocument = new Mapper\Map('Boomgo\\tests\\units\\Mock\\EmbedDocument');
        $mapEmbedDocument->add('mongoString', 'mongoString', 'getMongoString', 'setMongoString');
        $mapEmbedDocument->add('mongoNumber', 'mongoNumber', 'getMongoNumber', 'setMongoNumber');
        $mapEmbedDocument->add('mongoArray', 'mongoArray', 'getMongoArray', 'setMongoArray');

        $map = new Mapper\Map('Boomgo\\tests\\units\\Mock\\Document');
        $map->add('id', 'id', 'getId', 'setId');
        $map->add('mongoString', 'mongoString', 'getMongoString', 'setMongoString');
        $map->add('mongoPublicString', 'mongoPublicString');
        $map->add('mongoNumber', 'mongoNumber', 'getMongoNumber', 'setMongoNumber');
        $map->add('mongoDocument', 'mongoDocument', 'getMongoDocument', 'setMongoDocument', 'Document', $mapEmbedDocument);
        $map->add('mongoCollection', 'mongoCollection', 'getMongoCollection', 'setMongoCollection', 'Collection', $mapEmbedDocument);
        $map->add('mongoArray', 'mongoArray', 'getMongoArray', 'setMongoArray');

        return array('Boomgo\\tests\\units\\Mock\\Document' => $map, 'Boomgo\\tests\\units\\Mock\\EmbedDocument' => $mapEmbedDocument);
    }

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
        $document->mongoPublicString = 'a public string';
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
            'mongoPublicString' => 'a public string',
            'mongoNumber' => 1,
            'mongoDocument' => $embedArray,
            'mongoCollection' => $embedCollectionArray,
            'mongoArray' => array('an' => 'array', 8 => 1));

        return $array;
    }

    public function test__construct()
    {
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());
    }

    public function testGetCache()
    {
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());
        $this->assert
            ->object($mapper->getCache())
                ->isInstanceOf('Boomgo\tests\units\Mock\Cache');
    }

    public function testGetMap()
    {
        $this->mockGenerator->generate('Boomgo\Cache\CacheInterface');
        $this->mockGenerator->generate('Boomgo\Parser\ParserInterface');
        $mockCache = new \mock\Boomgo\Cache\CacheInterface;
        $mockParser = new \mock\Boomgo\Parser\ParserInterface;

        // Should call ParserInterface::buildMap() then CacheInterface::save() if CacheInterface::contains() return false
        $mockCache->getMockController()->contains = function($identifier){ return false; };
        $mapper = new Mapper\StrictMapper($mockParser, $mockCache);
        $this->assert
                ->when(function () use($mapper) {
                    $mapper->getMap('AFakeClass');
                })
                ->mock($mockCache)
                    ->call('contains')
                        ->once()
                    ->beforeMethodCall('save')
                        ->once()
                ->mock($mockParser)
                    ->call('buildMap')
                        ->once();

        // Should call CacheInterface::fetch() if CacheInterface::contains() returns true
        $mockCache->getMockController()->contains = function($identifier){ return true; };
        $mapper = new Mapper\StrictMapper($mockParser, $mockCache);
        $this->assert
                ->when(function () use($mapper) {
                    $mapper->getMap('AFakeClass');
                })
                ->mock($mockCache)
                    ->call('contains')
                        ->once()
                    ->beforeMethodCall('fetch')
                        ->once()
                ->mock($mockParser)
                    ->call('buildMap')
                        ->never();

        // Should return a cached map for the expected class
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache(true));
        $this->assert
            ->object($mapper->getMap('AFakeClass'))
            ->isInstanceOf('Boomgo\Mapper\Map');
    }

    public function testToArray()
    {
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());

        // Should throw exception if argument is not an object
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->toArray(1);;
                })
                ->isInstanceOf('InvalidArgumentException')
                ->hasMessage('Argument must be an object');

        // Should return an empty array when providing an empty object
        // Inject an empty map to the mocked parser
        $mapper->getParser()->mapList = array('\\stdClass' => new Mapper\Map('\\stdClass'));

        $array = $mapper->toArray(new \stdClass());

        $this->assert
            ->array($array)
                ->isEmpty();

        // Should return an empty array when providing object without value
        // Inject a map corresponding to the mocked document
        $mapper->getParser()->mapList = $this->mapProvider();

        $document = new Mock\Document();
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
                ->isEmpty();

        // Should return an array containing keys that have a non-null value
        $document = new Mock\Document();
        $document->setMongoString('the only value');

        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
                ->hasSize(1)
                ->isIdenticalTo(array('mongoString' => 'the only value'));

        // Should return a complete array
        $document = $this->documentProvider();

        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
                ->hasSize(7)
                ->isIdenticalTo($this->arrayProvider());
    }

    public function testHydrate()
    {
        // Inject a pre-built Map for the test into the mocked parser
        $mapper = new Mapper\StrictMapper(new Mock\Parser($this->mapProvider()), new Mock\Cache());

        $array = $this->arrayProvider();
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $object = $mapper->hydrate($ns.'Document', $array);

        // Should hydrate the root object
        $this->assert
            ->object($object)
                ->isInstanceOf($ns.'Document')
            ->string($object->getId())
                ->isEqualTo('an identifier')
            ->string($object->getMongoString())
                ->isEqualTo('a string')
            ->integer($object->getMongoNumber())
                ->isEqualTo(1);

        // Should hydrate a single embedded document
        $embedObject = $object->getMongoDocument();
        $this->assert
            ->object($embedObject)
                ->isInstanceOf($ns.'EmbedDocument')
            ->string($embedObject->getMongoString())
                ->isEqualTo('an embed string')
            ->integer($embedObject->getMongoNumber())
                ->isEqualTo(2)
            ->array($embedObject->getMongoArray())
                ->isEqualTo(array('an' => 'embed array', 7 => 2));

        // Should hydrate an embedded collection
        $embedCollection = $object->getMongoCollection();
        $this->assert
            ->array($embedCollection)
                ->hasSize(3)
            ->object($embedCollection[0])
                ->isInstanceOf($ns.'EmbedDocument')
            ->object($embedCollection[1])
                ->isInstanceOf($ns.'EmbedDocument')
            ->object($embedCollection[2])
                ->isInstanceOf($ns.'EmbedDocument');
    }

    public function testHydrateEmbed()
    {
        // Inject a pre-built Map for the test into the mocked parser
        $mapper = new Mapper\StrictMapper(new Mock\Parser($this->mapProvider()), new Mock\Cache());

        // $array = $this->arrayProvider();
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        // Should throws exception if a key defines an embedded document & don't provide an array
        $array = array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoPublicString' => 'a public string',
            'mongoNumber' => 1,
            'mongoDocument' => 'an invalid value for the key',
            'mongoArray' => array('an' => 'array', 8 => 1));

        $this->assert
            ->exception(function() use ($mapper, $array, $ns){
                $mapper->hydrate($ns.'Document', $array);
                })
                ->isInstanceOf('RuntimeException')
                ->hasMessage('Key "mongoDocument" defines an embedded document or collection and expects an array of values');

        // Should throws exception if a key defines an embedded document & don't provide an associative array
        $array = array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoPublicString' => 'a public string',
            'mongoNumber' => 1,
            'mongoDocument' => array('an','invalid','array'),
            'mongoArray' => array('an' => 'array', 8 => 1));

        $this->assert
            ->exception(function() use ($mapper, $array, $ns){
                $mapper->hydrate($ns.'Document', $array);
                })
                ->isInstanceOf('RuntimeException')
                ->hasMessage('Key "mongoDocument" defines an embedded document and expects an associative array of values');

        // Should throws exception if a key defines an embedded collection & don't provide a numeric-indexed array
        $array = array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoPublicString' => 'a public string',
            'mongoNumber' => 1,
            'mongoCollection' => array('an' => 'invalid','array'),
            'mongoArray' => array('an' => 'array', 8 => 1));

        $this->assert
            ->exception(function() use ($mapper, $array, $ns){
                $mapper->hydrate($ns.'Document', $array);
                })
                ->isInstanceOf('RuntimeException')
                ->hasMessage('Key "mongoCollection" defines an embedded collection and expects an numeric indexed array of values');
    }

    public function testCreateInstance()
    {
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        // Sould throw exception if constructor has mandatory prerequesite
        $this->assert
            ->exception(function() use ($mapper,$ns) {
                    $mapper->hydrate($ns.'DocumentConstructRequired', array('_id' => 1));
                })
                ->isInstanceOf('RuntimeException')
                ->hasMessage('Unable to hydrate an object requiring constructor param');
    }
}