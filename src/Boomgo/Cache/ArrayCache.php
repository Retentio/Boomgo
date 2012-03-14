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

namespace Boomgo\Cache;

/**
 * Array cache
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class ArrayCache implements CacheInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * Constructor initializes empty data array
     */
    public function __construct()
    {
        $this->data = array();
    }

    /**
     * Check if a cached entry exists
     *
     * @param string $identifier Unique cache identifier
     *
     * @return boolean
     */
    public function has($identifier)
    {
        return isset($this->data[$identifier]);
    }

    /**
     * Return a cached data
     *
     * @param string $identifier Unique cache identifier
     *
     * @return mixed
     */
    public function get($identifier)
    {
        return ($this->has($identifier)) ? $this->data[$identifier] : null;
    }

    /**
     * Cache data
     *
     * @param string  $identifier Unique cache identifier
     * @param mixed   $data       Data to be cached
     * @param integer $ttl        Time To Live in second
     */
    public function add($identifier, $data, $ttl = 0)
    {
        $this->data[$identifier] = $data;
    }

    /**
     * Delete a cached entry
     *
     * @param string $identifier Unique cache identifier
     */
    public function remove($identifier)
    {
        unset($this->data[$identifier]);
    }

   /**
     * Clear all cache
     */
    public function clear()
    {
        $this->data = array();
    }
}