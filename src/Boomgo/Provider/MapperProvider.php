<?php

namespace Boomgo\Provider;

use Boomgo\Cache\CacheInterface;

class MapperProvider extends BaseProvider
{
    public function __construct($namespace, $documentNamespace, CacheInterface $cache);
    {
        $this->documentNamespace = $documentNamespace;
        $this->namespace = $namespace;
        $this->setCache($cache);
    }

    protected function createInstance($fqdn)
    {
        $mapperClass = str_replace($this->documentNamespace, $this->namespace, $fqdn).'Mapper';
        return new $mapperClass($this);
    }
}