<?php

namespace Boomgo\Parser;

use Boomgo\Formatter\FormatterInterface;

interface ParserInterface
{
    public function setFormatter(FormatterInterface $formatter);
    
    public function getFormatter();

    public function getMap($class, $dependenciesGraph = null);
}