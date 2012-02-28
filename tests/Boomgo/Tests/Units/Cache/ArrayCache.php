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

namespace Boomgo\Tests\Units\Cache;

use Boomgo\Tests\Units\Test;
use Boomgo\Cache;

/**
 * ArrayCache tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class ArrayCache extends Test
{
    public function testHas()
    {
        $cache = new Cache\ArrayCache();

        // Should return false when the resource don't exist in cache
        $this->assert
            ->boolean($cache->has('identifier'))
                ->isFalse();

        // Should return true when the resource is cached
        $cache->add('identifier', 'dummy');
        $this->assert
            ->boolean($cache->has('identifier'))
                ->isTrue();
    }

    public function testGet()
    {
        $cache = new Cache\ArrayCache();

        // Should return null when the resource don't exist in cache
        $this->assert
            ->variable($cache->get('identifier'))
                ->isNull();

        // Should return the cached resource
        $cache->add('identifier', 'dummy');
        $this->assert
            ->string($cache->get('identifier'))
                ->isEqualTo('dummy');
    }

    public function testAdd()
    {
        $cache = new Cache\ArrayCache();

        // Should cache a resource
        $cache->add('identifier', 'dummy');
        $this->assert
            ->boolean($cache->has('identifier'))
                ->isTrue()
            ->string($cache->get('identifier'))
                ->isEqualTo('dummy');

    }

    public function testRemove()
    {
        $cache = new Cache\ArrayCache();

        // Should remove a specific cached resource
        $cache->add('identifier', 'dummy');
        $cache->add('another', 'resource');
        $cache->remove('identifier');
        $this->assert
            ->boolean($cache->has('identifier'))
                ->isFalse()
            ->boolean($cache->has('another'))
                ->isTrue();
    }

    public function testClear()
    {
        $cache = new Cache\ArrayCache();

        // Should remove a cached resource
        $cache->add('identifier', 'dummy');
        $cache->add('another', 'resource');
        $cache->clear();
        $this->assert
            ->boolean($cache->has('identifier'))
                ->isFalse()
            ->boolean($cache->has('another'))
                ->isFalse();
    }
}