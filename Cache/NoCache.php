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
 * No cache
 *
 * Dummy cache implementation usefull for dev environment.
 *
 * @author  Ludovic Fleury <ludo.fleury@gmail.com>
 */
class NoCache implements CacheInterface
{
    /**
     * Do not really cache
     *
     * @param  string  $identifier Unique cache identifier
     * @param  mixed   $data       Data to cache
     * @param  integer $ttl        Time To Live
     * @return boolean
     */
    public function save($identifier, $data, $ttl = 0)
    {
        return true;
    }

    /**
     * Always return false
     *
     * @param  string  $identifier Unique cache identifier
     * @return boolean
     */
    public function contains($identifier)
    {
        return false;
    }

    /**
     * Always return null
     *
     * @param  string  $identifier Unique cache identifier
     * @return null
     */
    public function fetch($identifier)
    {
        return null;
    }

    /**
     * Do nothing
     *
     * @param  string  $identifier Unique cache identifier
     * @return boolean
     */
    public function delete($identifier)
    {
        return true
    }
}