<?php

namespace Boomgo\Provider;

use Boomgo\Cache\CacheInterface;

class RepositoryProvider extends BaseProvider
{
    private $connection;

    private $mapperProvider;

    public function __construct($namespace, $documentNamespace, CacheInterface $cache, \Mongo $connection, MapperProvider $mapperProvider)
    {
        $this->documentNamespace = $documentNamespace;
        $this->connection = $connection;
        $this->namespace = $namespace;
        $this->setCache($cache);
        $this->setMapperprovider($mapperProvider);
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

    protected function createInstance($fqdn)
    {
        $repositoryClass = str_replace($this->documentNamespace, $this->namespace, $fqdn).'Repository';
        return new $repositoryClass($fqdn, $this->connection, $this->mapperProvider);
    }
}