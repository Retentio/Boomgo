<?php

namespace Boomgo\tests\units\Mock;

use Boomgo\Cache\CacheInterface;
use Boomgo\Mapper\Map;

/**
 * Dummy cache
 */
class Cache implements CacheInterface
{
    public function __construct($emulateCached = false)
    {
        $this->emulateCached = $emulateCached;
        $this->cached = array();
    }

    public function save($identifier, $data, $ttl = 0)
    {
        $this->cached[$identifier] = true;
    }

    public function contains($identifier)
    {
        return $this->emulateCached;
    }

    public function fetch($identifier)
    {
        return new Map($identifier);
    }

    public function delete($identifier)
    {
        return true;
    }
}