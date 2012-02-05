<?php

namespace Boomgo\tests\units\Mock;

use Boomgo\Mapper\Map;
use Boomgo\Parser\ParserInterface;
use Boomgo\Formatter\FormatterInterface;

class Parser implements ParserInterface
{
    /**
     * Mock implementation, allows you to pass a list of pre-built map
     * where key is the FQDN & value are the map
     * @var array
     */
    public $mapList;

    /**
     * __construct
     *
     * Mock implementation, allows you to pass a pre-built map
     *
     * @param array $map
     */
    public function __construct($mapList = array())
    {
        $this->mapList = $mapList;
    }

    public function setFormatter(FormatterInterface $formatter)
    {
    }

    public function getFormatter()
    {
    }

    public function buildMap($class, $dependenciesGraph = null)
    {
        return (isset($this->mapList[$class])) ? $this->mapList[$class] : new Map($class);
    }
}