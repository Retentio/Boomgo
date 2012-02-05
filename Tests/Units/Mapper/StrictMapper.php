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
use Boomgo\Mapper\Map;

use Boomgo\Cache\CacheInterface;

use Boomgo\tests\units\Mock;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Mapper/Map.php';

include __DIR__.'/../../../Mapper/MapperInterface.php';
include __DIR__.'/../../../Mapper/MapperProvider.php';
include __DIR__.'/../../../Mapper/StrictMapper.php';

include __DIR__.'/../../../Parser/ParserInterface.php';
include __DIR__.'/../../../Parser/ParserProvider.php';

include __DIR__.'/../../../Cache/CacheInterface.php';

include __DIR__.'/../../../Formatter/FormatterInterface.php';

include __DIR__.'/../Mock/Cache.php';
include __DIR__.'/../Mock/Document.php';
include __DIR__.'/../Mock/Formatter.php';
include __DIR__.'/../Mock/Parser.php';

/**
 * StrictMapper tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class StrictMapper extends \mageekguy\atoum\test
{
    public function mapProvider()
    {
        $mapEmbedDocument = new Map('Boomgo\\tests\\units\\Mock\\EmbedDocument');
        $mapEmbedDocument->add('mongoString', 'mongoString', 'getMongoString', 'setMongoString');
        $mapEmbedDocument->add('mongoNumber', 'mongoNumber', 'getMongoNumber', 'setMongoNumber');
        $mapEmbedDocument->add('mongoArray', 'mongoArray', 'getMongoArray', 'setMongoArray');

        $map = new Map('Boomgo\\tests\\units\\Mock\\Document');
        $map->add('id', 'id', 'getId', 'setId');
        $map->add('mongoString', 'mongoString', 'getMongoString', 'setMongoString');
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
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());
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
        $mapper = new Mapper\StrictMapper(new Mock\Parser(), new Mock\Cache());

        // Should throw exception if argument is not an object
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->toArray(1);;
                })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an object');

        // Should return an empty array when providing an empty object
        // Inject an empty map to the mock parser
        $mapper->getParser()->map = array('\\stdClass' => new Map('\\stdClass'));

        $array = $mapper->toArray(new \stdClass());

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an empty array when providing object without value
        // Inject a map corresponding to the mock document
        $mapper->getParser()->map = $this->mapProvider();

        $document = new Mock\Document();
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return a complete array
        $document = $this->documentProvider();

        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->hasSize(6)
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