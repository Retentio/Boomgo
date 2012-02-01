<?php

namespace Boomgo\tests\units\Mapper;

use Boomgo\Cache;
use Boomgo\Mapper;
use Boomgo\Parser;

use Boomgo\tests\units\Mock;

include __DIR__.'/MapperProvider.php';
include __DIR__.'/../../../Mapper/ProdMapper.php';

class ProdMapper extends MapperProvider
{
    public function mapperProvider()
    {
        return new Mapper\ProdMapper(new Mock\Cache());
    }
}