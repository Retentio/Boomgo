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

namespace Boomgo;

/**
 * Provider
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Manager
{
    /**
     * @var string Mappers namespace
     */
    private $mapperNamespace;

    /**
     * @var array Identity map for the Mappers
     */
    private $cache;

    public function __construct($mapperNamespace, CacheInterface $cache)
    {
        $this->cache($cache);
    }

    /**
     * Return the cache interface
     *
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return cache
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Return the Mapper bound to the class
     *
     * @param  string $class
     * @return MapperInterface
     */
    public function getMapper($class)
    {
        // @TODO refacto and move this chunk into a MapperFactory or into repositories
        if ($this->cache->has($class)) {
            $mapper = $this->get($class);
        } else {
            $array = explode('\\', $class);
            $mapper = new $mapperNamespace.'\\'.$array[count($array)-1];
            $this->cache->add($class, $mapper);
        }

        return $mapper;
    }

    /**
     * Hydrate
     *
     * @param  object $object
     * @param  array  $data
     * @return object
     */
    public function hydrate($object, array $data)
    {
        return $this->getMapper(get_class($object))->hydrate($object, $data);
    }

    /**
     * Unserialize
     *
     * @param  string $class An FQDN
     * @param  array  $data
     * @return object
     */
    public function unserialize($class, array $data)
    {
        return $this->getMapper($class)->unserialize($data);
    }

    /**
     * Serialize
     *
     * @param  object $object
     * @return array
     */
    public function serialize($object)
    {
        return $this->getMapper(get_class($object))->serialize($object);
    }
}