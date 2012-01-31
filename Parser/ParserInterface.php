<?php

namespace Boomgo\Parser;

use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;

interface ParserInterface
{
    public function setFormatter(FormatterInterface $formatter);
    
    public function getFormatter();

    public function setCache(CacheInterface $cache);

    public function getCache();

    public function getMap($class);
}