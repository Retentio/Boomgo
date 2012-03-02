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

namespace Boomgo\Tests\Units\Loader;

use Boomgo\Tests\Units\Test;
use Boomgo\Loader;

/**
 * File Loader
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */

class FileLoader extends Test
{
    private $fixtures = array();
    private $directory = __DIR__;

    public function test__construct()
    {
        // Should be able to define the directory
        $file = new Loader\FileLoader($this->directory);
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);
    }

    public function testSetDirectory()
    {
        $file = new Loader\FileLoader();

        // Should remove trailing /
        $file->setDirectory($this->directory.'/');
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove trailing \
        $file->setDirectory($this->directory.'\\');
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove trailing DIRECTORY_SEPARATOR
        $file->setDirectory($this->directory.DIRECTORY_SEPARATOR);
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);

        // Should remove all trailing \,/ and DIRECTORY_SEPARATOR
        $file->setDirectory($this->directory.'/'.'\\'.DIRECTORY_SEPARATOR);
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);

        // Should throw exception if directory do not exist
        $directory = $this->directory;
        $this->assert
            ->exception(function() use ($file, $directory) {
                $file->setDirectory($directory.DIRECTORY_SEPARATOR.'unknowndirectory');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Directory must be valid and writable');
    }

    public function testLoad()
    {
        $loader = new Loader\FileLoader($this->directory);

        // Should return unserialized data from a cached file
        $this->fixtureGenerator('test_loader_file', 's:25:"my data for the load test";');

        $this->assert
            ->string($loader->load('test_loader_file'))
            ->isEqualTo('my data for the load test');

        // Should throw exception if a cache file do not exists
        $this->assert
            ->exception(function() use ($loader) {
                $loader->load('an_unknown_file');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Invalid filename or not readable');
    }

    public function __destruct()
    {
        foreach ($this->fixtures as $fixture) {
            $this->fixtureCleaner($fixture);
        }
    }

    private function fixtureGenerator($filename, $data)
    {
        $filepath = $this->directory.DIRECTORY_SEPARATOR.$filename;
        $file = fopen($filepath, 'w');
        fwrite($file, $data);
        fclose($file);

        $this->fixtures[] = $filepath;
    }

    private function fixtureCleaner($filename)
    {
        if (!unlink($filename) || is_file($filename)) {
            trigger_error(sprintf('Unable to remove fixture file "%s" from the test', $filename), E_USER_WARNING);
        }
    }
}