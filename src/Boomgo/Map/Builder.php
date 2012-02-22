<?php

/**
 * This file is part of the Boomgo PHP ODM.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Map;

use Boomgo\Map\Map;
use Boomgo\Parser\ParserInterface;
use Boomgo\Formatter\FormatterInterface;
use Boomgo\Cache\CacheInterface;
use Symfony\Component\Finder\Finder;

/**
 * Builder
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Builder
{
    /**
     * @var Boomgo\Parser\ParserInterface
     */
    protected $parser;

    /**
     * @var Boomgo\Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * @var Boomgo\Cache\CacheInterface
     */
    protected $cache;

    /**
     * Initialize
     *
     * @param FormmatterInterface $formatter
     * @param string $annotation
     */
    public function __construct(ParserInterface $parser, FormatterInterface $formatter, CacheInterface $cache)
    {
        $this->setParser($parser);
        $this->setFormatter($formatter);
        $this->setCache($cache);
    }

    /**
     * Define the parser
     *
     * @param ParserInterface $parser [description]
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Return parser
     *
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
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
     * Define the cache
     *
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return the cache
     *
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Build Map(s) for an absolute directory or file path
     * @param string $path
     */
    public function build($path)
    {
        $processed = 0;
        $finder = new Finder();

        if (is_dir($path)) {
            $collection = $finder->files()->name('*')->in($path);
        } elseif (is_file($path)) {
            $collection = array($path);
        } else {
            throw new \InvalidArgumentException('Argument must be an aboslute directory or file path');
        }

        foreach ($collection as $resource) {
            if ($this->parser->supports($resource))
            {
                $metadata = $this->parser->parse($resource);
                $map = $this->buildMap($metadata);
                $this->cache->save($map->getClass(), $map);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Build a Map
     *
     * @param  array $metada
     * @return Map
     */
    private function buildMap(array $metadata)
    {
        $map = new Map($metadata['class']);

        foreach ($metadata['definitions'] as $metadataDefinition) {
            $definition = $this->buildDefinition($metadataDefinition);
            $map->add($definition);
        }

        return $map;
    }

    /**
     * Build a Definition
     *
     * @param  array  $metadata
     * @return Definition
     */
    private function buildDefinition(array $metadata)
    {
        if (!isset($metadata['attribute'])) {
            throw new \RuntimeException('Invalid metadata');
        }

        // @TODO Rethink this hacky method cause I hate annotation ?
        if (!isset($metadata['key'])) {
            $metadata['key'] = $this->formatter->toPhpAttribute($metadata['attribute']);
        }

        return new Definition($metadata);
    }
}