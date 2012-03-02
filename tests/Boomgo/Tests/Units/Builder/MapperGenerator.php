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

        $generator = new Builder\MapperGenerator($mapBuilder, $twigGenerator, __DIR__.'/../../../../../generated/Mapper');
        $generator->generate(array(__DIR__.'/../Fixture/AnnotedDocument.php', __DIR__.'/../Fixture/AnnotedDocumentEmbed.php'));
    }
}