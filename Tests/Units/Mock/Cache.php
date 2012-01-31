<?php

namespace Boomgo\tests\units\Mock;

use Boomgo\Cache\CacheInterface;

/**
 * Dummy cache
 */
class Cache implements CacheInterface
{
    public function save($identifier, $data, $ttl = 0)
    {
    }

    public function contains($identifier)
    {
        return false;
    }

    public function fetch($identifier)
    {   
    }

    public function delete($identifier)
    {   
    }
}