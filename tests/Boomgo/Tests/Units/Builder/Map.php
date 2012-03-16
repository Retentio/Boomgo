<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Tests\Units\Builder;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder as Src;

/**
 * Map tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Map extends Test
{
    public function test__construct()
    {
        // Should throw an error if argument string (FQDN) is not provided
        $this->assert
            ->error(function() {
                new Src\Map();
            })
            ->withType(E_RECOVERABLE_ERROR);

        // Should initialize object and define class & empty arrays
        $map = new Src\Map('FQDN');
        $this->assert
            ->string($map->getClass())
                ->isEqualTo('\FQDN')
            ->array($map->getMongoIndex())
                ->isEmpty()
            ->array($map->getDefinitions())
                ->isEmpty();
    }

    public function testGetClass()
    {
        // Should return the mapped FQDN with the first \
        $map = new Src\Map('FQDN');
        $this->assert
            ->string($map->getClass())
                ->isEqualTo('\FQDN');
    }

    public function testGetClassname()
    {
        // Should return the short class name without namespace part
        $map = new Src\Map('Vendor\\Package\\Subpackage\\Class');
        $this->assert
            ->string($map->getClassName())
                ->isEqualTo('Class');
    }

    public function testGetNamespace()
    {
        // Should return the namespace without the short class name part and with the first \
        $map = new Src\Map('Vendor\\Package\\Subpackage\\Class');
        $this->assert
            ->string($map->getNamespace())
                ->isEqualTo('\\Vendor\\Package\\Subpackage');
    }


    public function testAddDefinition()
    {
        // Should append an item to mongoIndex and Definition
        $map = new Src\Map('FQDN');
        $mockDefinition = $this->mockDefinitionProvider();
        $map->addDefinition($mockDefinition);
        $this->assert
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('key' => 'attribute'))
            ->array($map->getDefinitions())
                ->hasSize(1)
                ->isIdenticalTo(array('attribute' => $mockDefinition));
    }

    public function testHasDefinition()
    {
        // Should return false when looking for an unknown attribute
        $map = new Src\Map('FQDN');
        $this->assert
            ->boolean($map->hasDefinition('unkown'))
                ->isFalse();

        // Should return true for an existing "PHP attribute" or "MongoDB key"
        $mockDefinition = $this->mockDefinitionProvider();
        $map->addDefinition($mockDefinition);
        $this->assert
            ->boolean($map->hasDefinition('attribute'))
                ->isTrue()
            ->boolean($map->hasDefinition('key'))
                ->istrue();
    }

    public function testGetDefinition()
    {
        // Should return null when getting with an unknown attribute
        $map = new Src\Map('FQDN');
        $this->assert
            ->variable($map->getDefinition('unkown'))
                ->isNull();

        // Should return true when getting with an existing "PHP attribute" or "MongoDB key"
        $mockDefinition = $this->mockDefinitionProvider();
        $map->addDefinition($mockDefinition);
        $this->assert
            ->object($map->getDefinition('attribute'))
                ->isIdenticalTo($mockDefinition)
            ->object($map->getDefinition('key'))
                ->isIdenticalTo($mockDefinition);
    }

    private function mockDefinitionProvider()
    {
        $this->mock('Boomgo\\Builder\\Definition', '\\Mock\\Map', 'Definition');
        $mockController = new \mageekguy\atoum\mock\controller();
        $mockController->__construct = function() {};
        $mockController->injectInNextMockInstance();

        $mockDefinition = new \Mock\Map\Definition(array());
        $mockDefinition->getMockController()->getAttribute = 'attribute';
        $mockDefinition->getMockController()->getKey = 'key';

        return $mockDefinition;
    }
}