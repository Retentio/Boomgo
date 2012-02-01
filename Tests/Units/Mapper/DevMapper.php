<?php

namespace Boomgo\tests\units\Mapper;

use Boomgo\Cache;
use Boomgo\Mapper;
use Boomgo\Parser;

use Boomgo\tests\units\Mock;

include __DIR__.'/MapperProvider.php';
include __DIR__.'/../../../Mapper/DevMapper.php';

class DevMapper extends MapperProvider
{
    public function mapperProvider()
    {
        return new Mapper\DevMapper(new Parser\AnnotationParser(new Mock\Formatter(), new Mock\Cache()));
    }
}