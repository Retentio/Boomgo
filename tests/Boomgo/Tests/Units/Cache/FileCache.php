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
 * FileCache tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class FileCache extends Test
{
    private $directory = __DIR__;

    public function test__construct()
    {
        // Should implement CacheInterface
        $this->assert
            ->class('Boomgo\Cache\FileCache')
            ->hasInterface('Boomgo\Cache\CacheInterface');

        // Should be able to define the cache directory
        $cache = new Cache\FileCache($this->directory);
        $this->assert
            ->string($cache->getDirectory())
            ->isEqualTo($this->directory);
    }

    public function testSetDirectory()
    {
        $cache = new Cache\FileCache();

        // Should remove trailing /
        $cache->setDirectory($this->directory.'/');
        $this->assert
            ->string($cache->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove trailing \
        $cache->setDirectory($this->directory.'\\');
        $this->assert
            ->string($cache->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove trailing DIRECTORY_SEPARATOR
        $cache->setDirectory($this->directory.DIRECTORY_SEPARATOR);
        $this->assert
            ->string($cache->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove all trailing \,/ and DIRECTORY_SEPARATOR
        $cache->setDirectory($this->directory.'/'.'\\'.DIRECTORY_SEPARATOR);
        $this->assert
            ->string($cache->getDirectory())
            ->isEqualTo($this->directory);

        // Should throw exception if directory do not exist
        $directory = $this->directory;
        $this->assert
            ->exception(function() use ($cache, $directory) {
                $cache->setDirectory($directory.DIRECTORY_SEPARATOR.'unknowndirectory');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Directory must be valid and writable');
    }

    public function testSave()
    {
        $cache = new Cache\FileCache($this->directory);

        // Should write a file
        $result = $cache->save('test','my data for the cache test');
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'test';

        $this->assert
            ->boolean(file_exists($filepath))
            ->isTrue();

        // Should create a valid file
        $this->assert
            ->boolean(is_file($filepath))
            ->isTrue();

        // Should create a readable file
        $this->assert
            ->boolean(is_readable($filepath))
            ->isTrue();

        // Should cache serialized data
        $this->assert
            ->string(file_get_contents($filepath))
            ->isEqualTo('s:26:"my data for the cache test";');

        // Should return boolean true if the data had been cached
        $this->assert
            ->boolean($result)
            ->isTrue();

        $this->clean($filepath);

        // Should write a file with a valid filename (replacing namespace \ by _)
        $cache->save('testns\testsubns\test','my data for the cache test with namespace');
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'testns_testsubns_test';

        $this->assert
            ->boolean(file_exists($filepath) && is_file($filepath) && is_readable($filepath))
            ->isTrue();

        $this->clean($filepath);
    }

    public function testContains()
    {
        $cache = new Cache\FileCache($this->directory);

        // Should return false if the cached file do not exists
        $this->assert
            ->boolean($cache->contains('an_unknown_cached_map'))
            ->isFalse();

        // Should return true if the cached file exists
        $mockCacheFilepath = $this->directory.DIRECTORY_SEPARATOR.'contains_mock_cache_file';
        touch($mockCacheFilepath);

        $this->assert
            ->boolean($cache->contains('contains_mock_cache_file'))
            ->isTrue();

        $this->clean($mockCacheFilepath);
    }

    public function testFetch()
    {
        $cache = new Cache\FileCache($this->directory);

        // Should return unserialized data from a cached file
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'fetch_mock_cache_file';
        $file = fopen($filepath, 'w');
        fwrite($file, 's:26:"my data for the cache test";');
        fclose($file);

        $this->assert
            ->string($cache->fetch('fetch_mock_cache_file'))
            ->isEqualTo('my data for the cache test');

        $this->clean($filepath);

        // Should throw exception if a cache file do not exists
        $this->assert
            ->exception(function() use ($cache) {
                $cache->fetch('an_unknown_cached_map');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Invalid filename or not readable');
    }

    public function testDelete()
    {
        $cache = new Cache\FileCache($this->directory);

        // Should delete a cached file and return true
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'delete_mock_cache_file';
        touch($filepath);

        $deleted = $cache->delete('delete_mock_cache_file');

        $this->assert
            ->boolean(file_exists($filepath))
            ->isFalse();

        $this->assert
            ->boolean($deleted)
            ->isTrue();

        // Should throw exception if a cache file do not exists
        $this->assert
            ->exception(function() use ($cache) {
                $cache->delete('an_unknown_cached_map');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Invalid filename or not readable');
    }

    private function clean($filename)
    {
        if (!unlink($filename) || is_file($filename)) {
            throw new \RuntimeException('Test warning : unable to remove file from the test');
        }
    }
}