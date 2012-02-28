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

namespace Boomgo\Loader;

use Boomgo\Adapter\FileAdapter;

/**
 * MapperInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class FileLoader extends FileAdapter implements LoaderInterface
{
    public function load($resource)
    {
        $filename = $this->getAbsoluteFilepath($resource);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unserialize(file_get_contents($filename, 'r'));
    }
}