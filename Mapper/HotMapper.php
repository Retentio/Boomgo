<?php

namespace Boomgo\Mapper;

use Boomgo\Parser\ParserInterface;

/**
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class HotMapper extends MapperProvider
{
    private $parser;

    /**
     * Constructor
     * 
     * @param FormatterInterface An key/attribute formatter
     * @param string $annotation The annotation used for mapping
     */
    public function __construct(ParserInterface $parser)
    {
        $this->setParser($parser);
    }

    /**
     * Define the parser to use
     * 
     * @param ParserInterface $parser
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Return the parser used
     * 
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Return a map
     * Reused from the cache or on-the-fly parsed then cached
     * 
     * @param  string $class
     * @return Map
     */
    public function getMap($class)
    {
        return $this->parser->getMap($class);
    }
}