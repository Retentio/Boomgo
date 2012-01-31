<?php 

namespace Boomgo\Cache;

interface CacheInterface
{
    public function save($identifier, $data, $ttl = 0);

    public function contains($identifier);

    public function fetch($identifier);

    public function delete($identifier);
}