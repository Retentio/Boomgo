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

namespace Boomgo\Builder;

use Boomgo\Map as Export;
use Boomgo\Builder\Map;
use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;
use Boomgo\Parser\ParserInterface;
use Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

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
     *
     * @param string $path
     */
    public function build($path)
    {
        $processed = array();
        $finder = new Finder();

        if (is_array($path)) {
            $collection = $path;
        } elseif (is_dir($path)) {
            $collection = $finder->files()->name('*')->in($path);
        } elseif (is_file($path)) {
            $collection = array($path);
        } else {
            throw new \InvalidArgumentException('Argument must be an array or absolute directory or file path');
        }

        foreach ($collection as $resource) {
            $resource = ($resource instanceof SplFileInfo) ? $resource->getPathName() : $resource;
            if ($this->parser->supports($resource))
            {
                $metadata = $this->parser->parse($resource);
                $map = $this->buildMap($metadata);

                $processed[$map->getClass()] = $map;
            }
        }

        foreach ($processed as $class => $map) {
            $map = $this->buildDependencie($map, $processed, array($map->getClass() => true), $map);
            $export = $this->export($map);
            $this->cache->save($export->class, $export);
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
        if (!isset($metadata['attribute']) && !isset($metadata['key'])) {
            throw new \RuntimeException('Invalid metadata should provide an attribute or a key');
        }

        // @TODO Rethink this hacky method cause I hate annotation ?
        if (!isset($metadata['key'])) {
            $metadata['key'] = $this->formatter->toMongoKey($metadata['attribute']);
        } elseif (!isset($metadata['attribute'])) {
            $metadata['attribute'] = $this->formatter->toPhpAttribute($metadata['key']);
        }

        return new Definition($metadata);
    }

    /**
     * Optimize dependencies
     *
     * @param  Map    $masterMap
     * @param  array  $availableMaps
     * @param  array  $dependencies
     * @param  Map    $subMap
     * @return Map    $masterMap
     */
    private function buildDependencie(Map $masterMap, array $availableMaps, array $dependencies, Map $subMap)
    {
        $definitions = $subMap->getDefinitions();
        foreach ($definitions as $definition) {

            if ($definition->isUserMapped()) {

                if (!isset($dependencies[$definition->getMappedClass()])) {
                    $dependencies[$definition->getMappedClass()] = true;

                    if (isset($availableMaps[$definition->getMappedClass()])) {
                        $masterMap->addDependency($availableMaps[$definition->getMappedClass()]);
                        $this->buildDependencie($masterMap, $availableMaps, $dependencies, $availableMaps[$definition->getMappedClass()]);
                    } else {
                        throw new \RuntimeException (sprintf('Unable to build dependencie "%s" for the map "%s"', $definition->getMappedClass(), $map->getClass()));
                    }
                }
            }
        }

        return $masterMap;
    }

    /**
     * Export the Map to a lightweight read-only Map
     *
     * @param  Boomgo\Map\Map $map
     * @return Boomgo\Map
     */
    private function export(Map $map)
    {
        $array = $map->toArray();
        $export = new Export();
        $export->class = $array['class'];
        $export->phpIndex = $array['phpIndex'];
        $export->mongoIndex = $array['mongoIndex'];
        $export->definitions = $array['definitions'];
        $export->dependencies = isset($array['dependencies']) ? $array['dependencies'] : null;

        return $export;
    }
}