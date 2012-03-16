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

use Boomgo\Provider\MapperProvider;

/**
 * MapperProvider
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
abstract class BaseMapper
{
    protected $mapperProvider;

    public function __construct(MapperProvider $mapperProvider)
    {
        $this->setMapperProvider($mapperProvider);
    }

    public function setMapperProvider(MapperProvider $mapperProvider)
    {
        $this->mapperProvider = $mapperProvider;
    }
    public function getMapperProvider()
    {
        return $this->mapperProvider;
    }

    /**
     * Create an instance from a Reflected class
     *
     * @param ReflectionClass $reflectedClass
     *
     * @throws RuntimeException If constructor requires parameter
     *
     * @return mixed
     */
    protected function createInstance(\ReflectionClass $reflectedClass)
    {
        $constructor = $reflectedClass->getConstructor();

        if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new \RuntimeException('Unable to hydrate an object requiring constructor param');
        }

        return $reflectedClass->newInstance();
    }

    /**
     * Normalize php data for mongo
     *
     * This code chunk was inspired by the Symfony framework
     * and is subject to the MIT license. Please see the LICENCE
     * at https://github.com/symfony/symfony
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     * Author Nils Adermann <naderman@naderman.de>
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->normalize($val);
            }

            return $data;
        }

        throw new \RuntimeException('An unexpected value could not be normalized: '.var_export($data, true));
    }

    /**
     * Serialize an embedded collection
     *
     * Return a collection of hydrated objects
     *
     * @param MapperInterface $mapper
     * @param array           $collection
     *
     * @return array
     */
    protected function serializeEmbeddedCollection(MapperInterface $mapper, array $collection)
    {
        $data = array();
        foreach ($collection as $object) {
            $data[] = $mapper->serialize($object);
        }

        return $data;
    }

    /**
     * Unserialize an embedded collection
     *
     * Return a collection of serialized objects (arrays)
     *
     * @param MapperInterface $mapper
     * @param array           $data
     *
     * @return array
     */
    protected function unserializeEmbeddedCollection(MapperInterface $mapper, array $data)
    {
        $collection = array();
        foreach ($data as $document) {
            $collection[] = $mapper->unserialize($document);
        }

        return $collection;
    }
}