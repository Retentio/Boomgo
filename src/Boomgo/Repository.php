<?php

namespace Boomgo;

use Boomgo\Provider\MapperProvider;

abstract class Repository
{
    protected $documentClass;

    protected $connection;

    protected $mapperProvider;

    protected $mapper;

    abstract public function getDatabase();

    abstract public function getCollection();

    public function __construct($documentClass, \Mongo $connection, MapperProvider $mapperProvider)
    {
        $this->setDocumentClass($documentClass);
        $this->setConnection($connection);
        $this->setMapperProvider($mapperProvider);
    }

    public function setDocumentClass($documentClass)
    {
        $this->documentClass = $documentClass;
    }

    public function getDocumentClass()
    {
        return $this->documentClass;
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

    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = $this->mapperProvider->get($this->documentClass);
        }
        return $this->mapper;
    }

    public function count(array $selector = array())
    {
        return $this->connection
            ->selectDB($this->getDatabase())
            ->selectCollection($this->getCollection())
            ->count($selector);
    }
}