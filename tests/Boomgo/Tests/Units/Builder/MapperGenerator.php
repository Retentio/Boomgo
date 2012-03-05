<?php

namespace Boomgo\Tests\Units\Builder;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder;

class MapperGenerator extends Test
{
    public function test__construct()
    {
        $this->mockFactory();
        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();
        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);
        $mockTwigGenerator = new \Mock\Builder\TwigGenerator();

        // Should throw exception if options namespace models & mappers aren't defined.
        $this->assert
            ->exception(function() use ($mockMapBuilder, $mockTwigGenerator) {
                $mapperGenerator = new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator, array());
            })
                ->isInstanceOf('InvalidArgumentException')
                ->hasMessage('Options "namespace model" and "namespace mapper" must be defined')
             ->exception(function() use ($mockMapBuilder, $mockTwigGenerator) {
                $mapperGenerator = new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator, array('namespace' => array('models' => true)));
            })
                ->isInstanceOf('InvalidArgumentException')
                ->hasMessage('Options "namespace model" and "namespace mapper" must be defined')
             ->exception(function() use ($mockMapBuilder, $mockTwigGenerator) {
                $mapperGenerator = new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator, array('namespace' => array('mappers' => true)));
            })
                ->isInstanceOf('InvalidArgumentException')
                ->hasMessage('Options "namespace model" and "namespace mapper" must be defined');
    }

    public function testGetMapBuilder()
    {
        $mapperGenerator = $this->MapperGeneratorProvider(array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
        $this->assert
            ->object($mapperGenerator->getMapBuilder())
                ->isInstanceOf('Boomgo\\Builder\\MapBuilder');
    }

    public function testGetTwigGenerator()
    {
        $mapperGenerator = $this->MapperGeneratorProvider(array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
        $this->assert
            ->object($mapperGenerator->getTwigGenerator())
                ->isInstanceOf('TwigGenerator\\Builder\\Generator');
    }

    public function testGenerate()
    {
        $this->mockFactory();
        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();
        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);
        $mockTwigGenerator = new \Mock\Builder\TwigGenerator();

        $this->mock('Boomgo\\Builder\\Map', '\\Mock\\Builder', 'Map');
        $mockMap = new \Mock\Builder\Map('Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted\\Document');
        $mockMap->getMockController()->getClassName = function() { return 'Document'; };
        $mockMap->getMockController()->getNamespace = function() { return 'Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted'; };

        $mockMapBuilder->getMockController()->build = function() use ($mockMap) { return array($mockMap); };
        $mockTwigGenerator->getMockController()->writeOnDisk = function() {};

        $mapperGenerator = new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator, array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
        $this->assert
            ->variable($mapperGenerator->generate(array(__DIR__.'/../Fixture/AnotherAnnoted/Document.php')))
            ->mock($mockTwigGenerator)
                ->call('writeOnDisk')
                    ->once();

        // @TODO test generated code (functional test)
        // $formatter = new \Boomgo\Formatter\Underscore2CamelFormatter();
        // $parser = new \Boomgo\Parser\AnnotationParser();
        // $mapBuilder = new \Boomgo\Builder\MapBuilder($parser, $formatter);
        // $twigGenerator = new \TwigGenerator\Builder\Generator();
        // $generator = new Builder\MapperGenerator($mapBuilder, $twigGenerator, array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
        // $generator->generate(array(__DIR__.'/../Fixture/Annoted/Document.php', __DIR__.'/../Fixture/Annoted/DocumentEmbed.php'));

    }

    private function mockFactory()
    {
        if (!class_exists('\\Mock\\Parser\\Parser') && !class_exists('\\Mock\\Formatter\\Formatter') &&
            !class_exists('\\Mock\\Builder\\MapBuilder') && !class_exists('\\Mock\\Builder\\TwigGenerator')) {
            $this->mock('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
            $this->mock('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
            $this->mock('Boomgo\\Builder\\MapBuilder', '\\Mock\\Builder', 'MapBuilder');
            $this->mock('TwigGenerator\\Builder\\Generator', '\\Mock\\Builder', 'TwigGenerator');
        }
    }

    private function MapperGeneratorProvider(array $options = array())
    {
        $this->mockFactory();

        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();
        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);
        $mockTwigGenerator = new \Mock\Builder\TwigGenerator();

        return new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator, $options);
    }
}