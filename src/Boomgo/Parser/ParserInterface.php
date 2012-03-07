<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Parser;

/**
 * ParserInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface ParserInterface
{
    public function getExtension();

    /**
     * Returns true if this class supports the given resource.
     *
     * @param  resource $resource
     * @param  string   $type
     * @return boolean
     */
    public function supports($resource);

    /**
     * Extract and return an array of metadata from a resource
     *
     * @param  string $filepath
     * @return array
     */
    public function parse($filepath);
}