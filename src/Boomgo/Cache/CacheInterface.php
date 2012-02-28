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

namespace Boomgo\Cache;

/**
 * Cache interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface CacheInterface
{
    /**
     * Check if a cached entry exists
     *
     * @param  string  $identifier Unique cache identifier
     * @return boolean
     */
    public function has($identifier);

    /**
     * Return a cached data
     *
     * @param  string  $identifier Unique cache identifier
     * @return mixed
     */
    public function get($identifier);

    /**
     * Cache data
     *
     * @param  string  $identifier Unique cache identifier
     * @param  mixed   $data       Data to be cached
     * @param  integer $ttl        Time To Live in second
     */
    public function add($identifier, $data, $ttl = 0);

    /**
     * Delete a cached entry
     *
     * @param  string  $identifier Unique cache identifier
     */
    public function remove($identifier);

    /**
     * Clear all cache
     */
    public function clear();
}