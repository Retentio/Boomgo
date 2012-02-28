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

namespace Boomgo\Tests\Units\Writer;

use Boomgo\Tests\Units\Test,
    Boomgo\Tests\Units\Adapter\FileAdapter;
use Boomgo\Writer;

/**
 * File Writer
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */

class FileWriter extends FileAdapter
{
    private $fixtures = array();
    private $directory = __DIR__;

    public function testWrite()
    {
        $writer = new Writer\FileWriter($this->directory);

        // Should write a file
        $result = $writer->write('test', 'my data for the write test');
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'test';
        $this->fixtures[] = $filepath;

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
            ->isEqualTo('s:26:"my data for the write test";');

        // Should return boolean true if the data had been cached
        $this->assert
            ->boolean($result)
            ->isTrue();

        // Should write a file with a valid filename (replacing namespace \ by _)
        $writer->write('testns\testsubns\test','my data for the write test with namespace');
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'testns_testsubns_test';
        $this->fixtures[] = $filepath;

        $this->assert
            ->boolean(file_exists($filepath) && is_file($filepath) && is_readable($filepath))
            ->isTrue();
    }

    public function __destruct()
    {
        foreach ($this->fixtures as $fixture) {
            $this->fixtureCleaner($fixture);
        }
    }

    private function fixtureCleaner($filename)
    {
        if (!unlink($filename) || is_file($filename)) {
            trigger_error(sprintf('Unable to remove fixture file "%s" from the test', $filename), E_USER_WARNING);
        }
    }
}