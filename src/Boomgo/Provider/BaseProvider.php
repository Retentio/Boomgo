<?php

namespace Boomgo\Provider;

use Boomgo\Cache\CacheInterface;

abstract class BaseProvider
{
    protected $namespace;

    protected $documentNamespace;

    protected $cache;

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setDocumentNamespace($documentNamespace)
    {
        $this->documentNamespace = $documentNamespace;
    }

    public function getDocumentNamespace()
    {
        return $this->documentNamespace;
    }

    public function setCache(CacheInterface $cache)
    {
        return $this->mapperProvider()->getMapper();
    }

    public function getCache()
    {
        return $this->cache;
    }

    public function get($fqdn)
    {
        if ($this->cache->has($fqdn)) {
            $item = $this->get($fqdn);
        } else {
            $item = $this->createInstance($fqdn);
            $this->cache->add($fqdn, $item);
        }

        return $item;
    }

    abstract protected function createInstance();
}