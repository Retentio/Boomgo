<?php


namespace Boomgo\tests\units\Mapper;

use Boomgo\Mapper;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Mapper/Map.php';


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

        $this->assert
            ->exception(function() use ($map) {
                $map->addMutator('fakeKey', 'invalid Mutator');  
            })
            ->isInstanceOf('InvalidArgumentException')  
            ->hasMessage('Invalid php method name "invalid Mutator"');
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

         $index = $map->getIndex();

         $this->assert
            ->array($index)
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'));

        // Should add a accessor
        $map->add('fakeKey', 'fakeAttribute', 'fakeAccessor');

        $this->assert
            ->array($index)
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'));

        $this->assert
            ->array($map->getAccessors())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAccessor'));

        // Should add a mutator
        $map->add('fakeKey', 'fakeAttribute', null, 'fakeMutator');

        $this->assert
            ->array($index)
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'));

        $this->assert
            ->array($map->getMutators())
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeMutator'));

        // Should add a Map
        $map->add('fakeKey', 'fakeAttribute', 'fakeMutator', 'fakeAccessor', 'Document', new Mapper\Map('FakeMap'));

        $this->assert
            ->array($index)
            ->hasSize(1)
            ->isIdenticalTo(array('fakeKey' => 'fakeAttribute'));

        $this->assert
            ->array($map->getEmbedMaps())
            ->hasSize(1);
    }

    public function testGetKeys()
    {
        $map = new Mapper\Map('FakeMap');

        // Should get an array of keys
        $map->add('fakeKey', 'fakeAttribute');

        $this->assert
           ->array($map->getKeys())
           ->hasSize(1)
           ->isIdenticalTo(array('fakeKey'));
    }

    public function testGetAttributes()
    {
        $map = new Mapper\Map('FakeMap');

        // Should get an array of attributes
        $map->add('fakeKey', 'fakeAttribute');

        $this->assert
           ->array($map->getAttributes())
           ->hasSize(1)
           ->isIdenticalTo(array('fakeAttribute'));
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