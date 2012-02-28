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

namespace Boomgo\Tests\Units\Adapter;

use Boomgo\Tests\Units\Test;
use Boomgo\Adapter;

/**
 * File adapter
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */

class FileAdapter extends Test
{
    private $directory = __DIR__;

    public function test__construct()
    {
        $this->mock('Boomgo\\Adapter\\FileAdapter', '\\Mock\\Adapter', 'File');

        // Should be able to define the directory
        $file = new \Mock\Adapter\File($this->directory);
        $this->assert
            ->string($file->getDirectory())
            ->isEqualTo($this->directory);
    }

    public function testSetDirectory()
    {
        $this->mock('Boomgo\\Adapter\\FileAdapter', '\\Mock\\Adapter', 'File');

        $file = new \Mock\Adapter\File();

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
}