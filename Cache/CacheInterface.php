<?php

namespace Boomgo\Cache;

use Boomgo\Mapper\Map;

/**
 * Cache interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface CacheInterface
{
    /**
     * Cache data
     *
     * @param  string  $identifier Unique cache identifier
     * @param  mixed   $data       Data to be cached
     * @param  integer $ttl        Time To Live in second
     * @return boolean
     */
    public function save($identifier, $data, $ttl = 0);

    /**
     * Check if a cached entry exists
     *
     * @param  string  $identifier Unique cache identifier
     * @return boolean
     */
    public function contains($identifier);

    /**
     * Return a cached data
     *
     * @param  string  $identifier Unique cache identifier
     * @return Map
     */
    public function fetch($identifier);

    /**
     * Delete a cached entry
     *
     * @param  string  $identifier Unique cache identifier
     * @return boolean
     */
    public function delete($identifier);
}