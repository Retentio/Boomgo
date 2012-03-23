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

namespace Boomgo\Builder\Generator;

/**
 * GeneratorInterface
 *
 * @author David Guyon <dguyon@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * File generation process
     * 
     * @return bool
     */
    public function generate($sources, $generatedNamespace, $mappersNamespace, $generatedDirectory);
}