<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
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

use Boomgo\Formatter\FormatterInterface;
use Boomgo\Parser\ParserInterface;

/**
 * MapBuilder
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
     * Build Map(s) for an array of file
     *
     * @param array $files
     */
    public function build($files)
    {
        $processed = array();

        foreach ($files as $file) {
            if ($this->parser->supports($file))
            {
                $metadata = $this->parser->parse($file);
                $map = $this->buildMap($metadata);

                $processed[$map->getClass()] = $map;
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