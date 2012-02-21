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

namespace Boomgo\Tests\Units\Map;

use Boomgo\Tests\Units\Test;
use Boomgo\Map as Src;

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
                ->isEqualTo('FQDN')
            ->array($map->getPhpIndex())
                ->isEmpty()
            ->array($map->getMongoIndex())
                ->isEmpty()
            ->array($map->getDefinitions())
                ->isEmpty()
            ->array($map->getDependencies())
                ->isEmpty();
    }

    public function testAdd()
    {
        // Should append an item to phpIndex, mongoIndex and Definition
        $map = new Src\Map('FQDN');
        $mockDefinition = $this->mockDefinitionProvider();
        $map->add($mockDefinition);
        $this->assert
            ->array($map->getPhpIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('attribute' => 'key'))
            ->array($map->getMongoIndex())
                ->hasSize(1)
                ->isIdenticalTo(array('key' => 'attribute'))
            ->array($map->getDefinitions())
                ->hasSize(1)
                ->isIdenticalTo(array('attribute' => $mockDefinition));
    }

    public function testHas()
    {
        // Should return false when looking for an unknown attribute
        $map = new Src\Map('FQDN');
        $this->assert
            ->boolean($map->has('unkown'))
                ->isFalse();

        // Should return true for an existing "PHP attribute" or "MongoDB key"
        $mockDefinition = $this->mockDefinitionProvider();
        $map->add($mockDefinition);
        $this->assert
            ->boolean($map->has('attribute'))
                ->isTrue()
            ->boolean($map->has('key'))
                ->istrue();
    }

    public function testGet()
    {
        // Should return null when getting with an unknown attribute
        $map = new Src\Map('FQDN');
        $this->assert
            ->variable($map->get('unkown'))
                ->isNull();

        // Should return true when getting with an existing "PHP attribute" or "MongoDB key"
        $mockDefinition = $this->mockDefinitionProvider();
        $map->add($mockDefinition);
        $this->assert
            ->object($map->get('attribute'))
                ->isIdenticalTo($mockDefinition)
            ->object($map->get('key'))
                ->isIdenticalTo($mockDefinition);
    }

    private function mockDefinitionProvider()
    {
        $this->mock('Boomgo\\Map\\Definition', '\\Mock\\Map', 'Definition');
        $mockController = new \mageekguy\atoum\mock\controller();
        $mockController->__construct = function() {};
        $mockController->injectInNextMockInstance();

        $mockDefinition = new \Mock\Map\Definition(array());
        $mockDefinition->getMockController()->getAttribute = 'attribute';
        $mockDefinition->getMockController()->getKey = 'key';

        return $mockDefinition;
    }
}