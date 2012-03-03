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

use Boomgo\Builder\Map;
use Boomgo\Formatter\FormatterInterface;
use Boomgo\Parser\ParserInterface;
use Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

/**
 * Builder
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class MapBuilder
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
     * Initialize
     *
     * @param FormmatterInterface $formatter
     * @param string $annotation
     */
    public function __construct(ParserInterface $parser, FormatterInterface $formatter)
    {
        $this->setParser($parser);
        $this->setFormatter($formatter);
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
     * Build Map(s) for an absolute directory or file path
     *
     * @param string $path
     */
    public function build($path)
    {
        $collection = $this->load($path);
        $processed = array();

        foreach ($collection as $resource) {
            if ($this->parser->supports($resource))
            {
                $metadata = $this->parser->parse($resource);
                $map = $this->buildMap($metadata);

                $processed[$map->getClass()] = $map;
            }
        }

        return $processed;
    }

    /**
     * Return a collection of resource
     *
     * @param  string $path
     * @return array
     */
    private function load($path)
    {
        $finder = new Finder();
        $collection = array();

        if (is_array($path)) {
            foreach ($path as $resource) {
                $subcollection = array();
                $subcollection = $this->load($resource);
                $collection = array_merge($collection, $subcollection);
            }
        } elseif (is_dir($path)) {
            $files = $finder->files()->name('*.'.$this->parser->getExtension())->in($path);
            foreach ($files as $file) {
                $collection[] = $file->getPathName();
            }
        } elseif (is_file($path)) {
            $collection = array($path);
        } else {
            throw new \InvalidArgumentException('Argument must be an absolute directory or a file path or both in an array');
        }

        return $collection;
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
            $map->addDefinition($definition);
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

        $metadata['accessor'] = $this->formatter->getPhpAccessor($metadata['attribute']);
        $metadata['mutator'] = $this->formatter->getPhpMutator($metadata['attribute']);

        return new Definition($metadata);
    }
}