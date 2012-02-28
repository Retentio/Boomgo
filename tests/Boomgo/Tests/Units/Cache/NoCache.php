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
 * NoCache tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class NoCache extends Test
{
    public function testHas()
    {
        $cache = new Cache\NoCache();

        // Should return false
        $this->assert
            ->boolean($cache->has('identifier'))
            ->isFalse();
    }

    public function testGet()
    {
        $cache = new Cache\NoCache();

        // Should return null
        $this->assert
            ->variable($cache->get('identifier'))
            ->isNull();
    }
}