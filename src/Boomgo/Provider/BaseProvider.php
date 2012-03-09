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
        return $this->cache = $cache;
    }

    public function getCache()
    {
        return $this->cache;
    }
}