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

namespace Boomgo\tests\units\Cache;

use Boomgo\Cache;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';
require_once __DIR__.'/../../../Cache/CacheInterface.php';
require_once __DIR__.'/../../../Cache/NoCache.php';

/**
 * NoCache tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */

class NoCache extends \mageekguy\atoum\test
{
    public function testSave()
    {
        $cache = new Cache\NoCache();

        // Should return true
        $this->assert
            ->boolean($cache->save('identifier', 'data'))
            ->isTrue();
    }

    public function testContains()
    {
        $cache = new Cache\NoCache();

        // Should return false
        $this->assert
            ->boolean($cache->contains('identifier'))
            ->isFalse();
    }

    public function testFetch()
    {
        $cache = new Cache\NoCache();

        // Should return null
        $this->assert
            ->variable($cache->fetch('identifier'))
            ->isNull();
    }

    public function testDelete()
    {
        $cache = new Cache\NoCache();

        // Should return true
        $this->assert
            ->boolean($cache->delete('identifier'))
            ->isTrue();
    }
}