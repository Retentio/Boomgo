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

namespace Boomgo\Mapper;

use Boomgo\Map;

/**
 * MapperInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface MapperInterface
{
    /**
     * Return an mongoable array from an object
     *
     * @param  mixed $object
     * @return array
     */
    public function serialize($object);

    /**
     * Return an hydrated object from an MongoDB array
     *
     * @param  array $array
     * @return object
     */
    public function unserialize(array $array);

    /**
     * Hydrate a PHP object from a MongoDB array
     *
     * @param  mixed  $object
     * @param  array  $array
     * @return object
     */
    public function hydrate($object, array $array);
}