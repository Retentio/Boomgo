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
 * No cache
 *
 * Dummy cache implementation usefull for dev environment.
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class NoCache implements CacheInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $identifier Unique cache identifier
     *
     * @return boolean
     */
    public function has($identifier)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $identifier Unique cache identifier
     *
     * @return mixed
     */
    public function get($identifier)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string  $identifier Unique cache identifier
     * @param mixed   $data       Data to be cached
     * @param integer $ttl        Time To Live in second
     */
    public function add($identifier, $data, $ttl = 0)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param string $identifier Unique cache identifier
     */
    public function remove($identifier)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
    }
}