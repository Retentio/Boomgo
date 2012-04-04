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
     * @param mixed  $sources              Mapping source(s) directory
     * @param string $generatedNamespace   Namespace term for generated class (ex: Document, Model)
     * @param string $mappersNamespace     Base mappers namespace (ex: Mapper, Mapping)
     * @param string $generatedDirectory   Directory where classes are generated
     *
     * @return bool
     */
    public function generate($sources, $generatedNamespace, $mappersNamespace, $generatedDirectory);
}