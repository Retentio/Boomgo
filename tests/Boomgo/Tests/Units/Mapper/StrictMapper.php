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

namespace Boomgo\Tests\Units\Mapper;

use Boomgo\Tests\Units\Test;
use Boomgo\Mapper as Src;


/**
 * StrictMapper tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class StrictMapper extends Test
{
    public function testToArray()
    {
        $mapper = new Src\StrictMapper();
        $map = $mapper->loadMap();

        $mongoId = new \MongoId();
        $array = $mapper->toArray($map, $this->objectProvider($mongoId));

        $this->assert
            ->array($array)
                ->isIdenticalTo($this->arrayProvider($mongoId));
    }

    public function testHydrate()
    {
        $mapper = new Src\StrictMapper();
        $mongoId = new \MongoId();
        $map = $this->mapProvider();

        $object = $mapper->hydrate($map, $this->arrayProvider($mongoId));
        $this->assert
            ->object($object)
                ->isIdenticalTo($this->objectProvider($mongoId));
    }

    private function arrayProvider(\MongoId $mongoId)
    {
        $embedArray = array(
            'string' => 'embed string',
            'array'  => array('embed' => 'array', 'embed', 9 => 'marvellous'));

        $array = array(
            '_id' => $mongoId,
            'string'     => 'my string',
            'number'     => 5,
            'array'      => array('my' => 'array', 'is', 7 => 'fabulous'),
            'document'   => $embedArray,
            'collection' => array($embedArray, $embedArray));

        return $array;
    }

    private function objectProvider(\MongoId $mongoId)
    {
        $embedObject = new \Boomgo\Tests\Units\Fixture\AnnotedDocumentEmbed();
        $embedObject->setString('embed string');
        $embedObject->setArray(array('embed' => 'array', 'embed', 9 => 'marvellous'));

        $object = new \Boomgo\Tests\Units\Fixture\AnnotedDocument();
        $object->setId($mongoId);
        $object->setString('my string');
        $object->setNumber(5);
        $object->setArray(array('my' => 'array', 'is', 7 => 'fabulous'));
        $object->setDocument($embedObject);
        $object->setCollection(array($embedObject, $embedObject));

        return $object;
    }
}