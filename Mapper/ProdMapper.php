<?php

namespace Boomgo\Mapper;

use Boomgo\Cache\CacheInterface;

/**
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class ProdMapper extends MapperProvider
{
    private $cache;

    /**
     * Constructor
     * 
     * @param FormatterInterface An key/attribute formatter
     * @param string $annotation The annotation used for mapping
     */
    public function __construct(CacheInterface $cache)
    {
        $this->setCache($cache);
    }

    /**
     * Define the cache to use
     * 
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return the cache used
     * 
     * @return ParserInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Return a map for a class exclusively from the cache
     * 
     * @param  string $class
     * @return Map
     */
    public function getMap($class)
    {
        if(!$this->cache->contains($class)) {
            throw new \RuntimeException('Map cache for "'.$class.'" do not exists');
        }
        return $this->cache->fetch($class);
    }
}