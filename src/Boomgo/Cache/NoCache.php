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
     */
    public function has($identifier)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function add($identifier, $data, $ttl = 0)
    {
    }

    /**
     * {@inheritdoc}
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