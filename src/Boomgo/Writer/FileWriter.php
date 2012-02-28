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

namespace Boomgo\Writer;

use Boomgo\Adapter\FileAdapter;

/**
 * Writer
 *
 * Serialize & store the map on the disk.
 * The directory must exist and be writable.
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class FileWriter extends FileAdapter implements WriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write($identifier, $data)
    {
        $filename = $this->getAbsoluteFilepath($identifier);

        $file = fopen($filename, 'w');
        $success = fwrite($file, serialize($data));
        fclose($file);

        return (bool)$success;
    }
}