<?php

namespace Boomgo\Tests\Units\Builder;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder;

class MapperGenerator extends Test
{
    public function testGenerate()
    {
        $formatter = new \Boomgo\Formatter\Underscore2CamelFormatter();
        $parser = new \Boomgo\Parser\AnnotationParser();

        $mapBuilder = new \Boomgo\Builder\MapBuilder($parser, $formatter);

        $twigGenerator = new \TwigGenerator\Builder\Generator();

        $generator = new Builder\MapperGenerator($mapBuilder, $twigGenerator, array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
        $generator->generate(array(__DIR__.'/../Fixture/Annoted/Document.php', __DIR__.'/../Fixture/Annoted/DocumentEmbed.php'));
    }
}