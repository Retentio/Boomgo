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

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Mapper/Map.php';

/**
 * Map tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Map extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        // Should set the class name and the type
        $map = new Mapper\Map('FakeClassName');

        $this->assert
            ->string($map->getClass())
            ->isEqualTo('FakeClassName');
    }

    public function testSetGetClass()
    {
        // Should set and get class
        $map = new Mapper\Map('FakeMap');
        $map->setClass('AnotherFakeClassName');

        $this->assert
            ->string($map->getClass())
            ->isEqualTo('AnotherFakeClassName');
    }

    public function testAddEmbedType()
    {
         $map = new Mapper\Map('FakeMap');

        // Should set and get type
        $map->addEmbedType('fakeKey','Collection');

        $this->assert
            ->array($map->getEmbedTypes())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'COLLECTION'));

        // Should throw exception on invalid type
        $this->assert
            ->exception(function() use ($map) {
                $map->addEmbedType('fakeKey','AnUnknownType');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Unknown map type "AnUnknownType"');
    }

    public function testGetMutators()
    {
        $map = new Mapper\Map('FakeMap');

        $this->assert
            ->array($map->getMutators())
            ->isEmpty();
    }

    public function testGetEmbedMaps()
    {
        $map = new Mapper\Map('FakeMap');

        $this->assert
            ->array($map->getEmbedMaps())
            ->isEmpty();
    }

    public function testAddMutator()
    {
        $map = new Mapper\Map('FakeMap');

        // Should add a mutator to the related key
        $map->addMutator('fakeKey', 'fakeMutator');

        $this->assert
            ->array($map->getMutators())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeMutator'));
    }

    public function testAddEmbedMap()
    {
        $map = new Mapper\Map('FakeMap');

        // Should add a mutator to the related key
        $map->addEmbedMap('fakeKey', new Mapper\Map('FakeMap'));

        $this->assert
            ->array($map->getEmbedMaps())
            ->hasSize(1);
    }

    public function testAdd()
    {
         $map = new Mapper\Map('FakeMap');

         // Should add a basic key/attribute
         $map->add('fakeKey', 'fakeAttribute');

         $this->assert
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'))
            ->array($map->getPhpIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeAttribute' => 'fakeKey'));

        // Should add a accessor
        $map->add('fakeKey', 'fakeAttribute', 'fakeAccessor');

        $this->assert
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'))
            ->array($map->getPhpIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeAttribute' => 'fakeKey'));

        $this->assert
            ->array($map->getAccessors())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAccessor'));

        // Should add a mutator
        $map->add('fakeKey', 'fakeAttribute', null, 'fakeMutator');

        $this->assert
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'))
            ->array($map->getPhpIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeAttribute' => 'fakeKey'));

        $this->assert
            ->array($map->getMutators())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeMutator'));

        // Should add a Map
        $map->add('fakeKey', 'fakeAttribute', 'fakeMutator', 'fakeAccessor', 'Document', new Mapper\Map('FakeMap'));

        $this->assert
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'))
            ->array($map->getPhpIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('fakeAttribute' => 'fakeKey'));

        $this->assert
            ->array($map->getEmbedMaps())
            ->hasSize(1);
    }

    public function testGetAttributeFor()
    {
        $map = new Mapper\Map('FakeMap');

        // Should get an attribute for a key
        $map->add('fakeKey', 'fakeAttribute');

        $this->assert
           ->string($map->getAttributeFor('fakeKey'))
           ->isEqualTo('fakeAttribute');
    }

    public function testGetEmbedMapFor()
    {
        $map = new Mapper\Map('FakeMap');

        // Shouldget an embedded map for a key
        $map->addEmbedMap('fakeKey', new Mapper\Map('FakeMap'));

        $this->assert
            ->object($map->getEmbedMapFor('fakeKey'))
            ->isInstanceOf('Boomgo\Mapper\Map');
    }

    public function testHasMutator()
    {
        $map = new Mapper\Map('FakeMap');

        // Should return true if a mutator is binded to the key
        $map->addMutator('fakeKey', 'fakeMutator');

        $this->assert
            ->boolean($map->hasMutatorFor('fakeKey'))
            ->isTrue();

        // Should return false if a mutator do not exists for the key
        $this->assert
            ->boolean($map->hasMutatorFor('anUnknownKey'))
            ->isFalse();
    }

    public function testGetMutatorFor()
    {
        $map = new Mapper\Map('FakeMap');

        // Should return true if a mutator is binded to the key
        $map->addMutator('fakeKey', 'fakeMutator');

        $this->assert
            ->string($map->getMutatorFor('fakeKey'))
            ->isEqualTo('fakeMutator');
    }
}