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

use Boomgo\Map;

/**
 * Writer interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface WriterInterface
{
    /**
     * Write data
     *
     * @param  string  $identifier Unique resource identifier
     * @param  mixed   $data       Data to write
     * @return boolean $success    True if write is successfull
     */
    public function write($identifier, $data);
}