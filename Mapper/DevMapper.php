<?php

namespace Boomgo\Mapper;

use Boomgo\Parser\ParserInterface;

class DevMapper extends MapperProvider
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
     * Return always a fresh parsed Map
     * 
     * @param  string $class
     * @return Map
     */
    public function getMap($class)
    {
        return $this->parser->buildMap($class);
    }
}