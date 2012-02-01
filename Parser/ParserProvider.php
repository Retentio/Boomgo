<?php

namespace Boomgo\Parser;

use Boomgo\Mapper\Map;
use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;

abstract class ParserProvider implements ParserInterface
{
    protected $formatter;

    protected $cache;
    
    /**
     * Initialize
     * 
     * @param FormmatterInterface $formatter
     * @param string $annotation
     */
    public function __construct(FormatterInterface $formatter, CacheInterface $cache)
    {
        $this->setFormatter($formatter);
        $this->setCache($cache);
    }

    /**
     * Define the key/attribute formatter
     * 
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;    
    }

    /**
     * Return the key/attribute formatter
     * 
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Define the map cache
     * 
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return the map cache
     * 
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Return a map 
     * Fetch an already cached map or build then cache it
     * 
     * @param  string $class The (FQDN) class name
     * @return Map
     */
    public function getMap($class)
    {
        $reflectedClass = new \ReflectionClass($class);

        $map = new Map($class);

        if ($this->cache->contains($class) && !$refresh) {
            $map = $this->cache->fetch($class);
        } else {
            $map = $this->buildMap($class);
            $this->cache->save($class, $map);
        }
        return $map;
    }

    /**
     * Force a map to be cached
     * even if the cache already exists
     * 
     * @param  string $class The (FQDN) class name
     */
    public function cacheMap($class, $force = true)
    {
        $reflectedClass = new \ReflectionClass($class);

        $map = new Map($class);

        if (!$force && $this->cache->contains($class)) {
            return;
        }

        $map = $this->buildMap($class);
        $this->cache->save($class, $map);
    }

    /**
     * Manage and update map dependencies
     * 
     * @param  string $class            Class to add to the depencies list
     * @param  array  $dependeciesGraph Null or dependencie legacy
     * @return array
     */
    protected function updateDependencies($class, $dependenciesGraph)
    {
        if (null === $dependenciesGraph) { 
            $dependenciesGraph = array();
        }

        if (isset($dependenciesGraph[$class])) {
            throw new \RuntimeException('Cyclic dependency, a document cannot directly/indirectly be embed in itself');
        }

        $dependenciesGraph[$class] = true;

        return $dependenciesGraph;
    }

    abstract protected function buildMap($class, $dependenciesGraph = null);
}