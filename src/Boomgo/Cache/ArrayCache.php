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

    public function __construct()
    {
        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return isset($this->data[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        return ($this->has($identifier)) ? $this->data[$identifier] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function add($identifier, $data, $ttl = 0)
    {
        $this->data[$identifier] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        unset($this->data[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = array();
    }
}