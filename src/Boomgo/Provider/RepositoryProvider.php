<?php

namespace Boomgo\Provider;

use Boomgo\Cache\CacheInterface;

class RepositoryProvider extends BaseProvider
{
    private $connection;

    private $mapperProvider;

    private $baseNamespaces;

    public function __construct($namespace, $documentNamespace, CacheInterface $cache, \Mongo $connection, MapperProvider $mapperProvider)
    {
        $this->documentNamespace = $documentNamespace;
        $this->connection = $connection;
        $this->namespace = $namespace;
        $this->setCache($cache);
        $this->setMapperprovider($mapperProvider);
        $this->baseNamespaces = array();
    }

    public function setConnection(\Mongo $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setMapperProvider(MapperProvider $mapperProvider)
    {
        $this->mapperProvider = $mapperProvider;
    }

    public function getMapperProvider()
    {
        return $this->mapperProvider;
    }

    public function setBaseNamespaces($baseNamespaces)
    {
        $this->baseNamespaces;
    }

    public function getBaseNamespace()
    {
        return $this->baseNamespaces;
    }

    public function addBaseNamespace($key, $namespace)
    {
        $namespace = trim($namespace, '\\').'\\';
        $this->baseNamespaces[$key] = $namespace;
    }

    public function hasBaseNamespace($key)
    {
        return isset($this->baseNamespaces[$key]);
    }

    public function getBaseNamespace($key)
    {
        return ($this->hasBaseNamespace($key)) ? $this->baseNamespaces[$key] : null;
    }

    public function get($fqdn)
    {
        if (!strpos('\\', $fqdn)) {
            $key = (strpos('.', $fqdn)) ? $fqdn : 'default';

            if (!$this->hasBaseNamespace($key)) {
                throw new \RuntimeException(sprintf('Unknown namespace identifier "%s"', $key));
            }

            $namespace = $this->getBaseNamespace($key);
            $fqdn = $namespace.$fqdn;
        }

        if ($this->cache->has($fqdn)) {
            $repository = $this->cache->get($fqdn);
        } else {
            $repositoryClass = str_replace($this->documentNamespace, $this->namespace, $fqdn).'Repository';
            $repository = new $repositoryClass($fqdn, $this->connection, $this->mapperProvider);
            $this->cache->add($fqdn, $repository);
        }

        return $repository;
    }

    protected function createInstance($fqdn)
    {

        return
    }
}