<?php

namespace Boomgo\Provider;

use Boomgo\Cache\CacheInterface;

class MapperProvider extends BaseProvider
{
    public function __construct($namespace, $documentNamespace, CacheInterface $cache)
    {
        $this->documentNamespace = $documentNamespace;
        $this->namespace = $namespace;
        $this->setCache($cache);
    }

    public function get($fqdn)
    {
        if ($this->cache->has($fqdn)) {
            $mapper = $this->cache->get($fqdn);
        } else {
            $mapperClass = str_replace($this->documentNamespace, $this->namespace, $fqdn).'Mapper';
            $mapper = new $mapperClass($this);
            $this->cache->add($fqdn, $mapper);
        }

        return $mapper;
    }
}