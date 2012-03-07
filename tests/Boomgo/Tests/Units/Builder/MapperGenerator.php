<?php

namespace Boomgo\Tests\Units\Builder;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder;

class MapperGenerator extends Test
{
    public function testGetMapBuilder()
    {
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->object($mapperGenerator->getMapBuilder())
                ->isInstanceOf('Boomgo\\Builder\\MapBuilder');
    }

    public function testGetTwigGenerator()
    {
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->object($mapperGenerator->getTwigGenerator())
                ->isInstanceOf('TwigGenerator\\Builder\\Generator');
    }

    public function testLoad()
    {
        // Should throw an exception if argument is a string and not a valid file or directory
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->exception(function() use ($mapperGenerator) {
                $mapperGenerator->load('invalid path');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an absolute directory or a file path or both in an array');

        // Should throw an exception if argument is an array and an element is not a valid file or directory
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->exception(function() use ($mapperGenerator) {
                $mapperGenerator->load(array(__FILE__, 'invalid path'));
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an absolute directory or a file path or both in an array');


        // Should return an array containing the realpath of a filename when providing a filename
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->array($mapperGenerator->load(__DIR__.'/../Fixture/Annoted/Document.php'))
                ->hasSize(1)
                ->isIdenticalTo(array(realpath(__DIR__.'/../Fixture/Annoted/Document.php')));

        // Should return an array containing many files realpath when providing a directory
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->array($mapperGenerator->load(__DIR__.'/../Fixture/Annoted'))
                ->hasSize(2)
                ->isIdenticalTo (array(realpath(__DIR__.'/../Fixture/Annoted/Document.php'), realpath(__DIR__.'/../Fixture/Annoted/DocumentEmbed.php')));

        // Should return an array containing many files realpath when providing an array of directory
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->array($mapperGenerator->load(array(__DIR__.'/../Fixture/Annoted', __DIR__.'/../Fixture/AnotherAnnoted')))
                ->hasSize(3)
                ->isIdenticalTo(array(
                    realpath(__DIR__.'/../Fixture/Annoted/Document.php'),
                    realpath(__DIR__.'/../Fixture/Annoted/DocumentEmbed.php'),
                    realpath(__DIR__.'/../Fixture/AnotherAnnoted/Document.php')));

        // Should return an array containing many files realpath when providing an array mixed with directory and file
        $mapperGenerator = $this->MapperGeneratorProvider();
        $this->assert
            ->array($mapperGenerator->load(array(__DIR__.'/../Fixture/Annoted', __DIR__.'/../Fixture/AnotherAnnoted/Document.php')))
                ->hasSize(3)
                ->isIdenticalTo(array(
                    realpath(__DIR__.'/../Fixture/Annoted/Document.php'),
                    realpath(__DIR__.'/../Fixture/Annoted/DocumentEmbed.php'),
                    realpath(__DIR__.'/../Fixture/AnotherAnnoted/Document.php')));
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

        $mapperGenerator = new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator);
        $this->assert
            ->variable($mapperGenerator->generate(array(__DIR__.'/../Fixture/AnotherAnnoted/Document.php'), '/Mapper', '\\Mapper'))
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

    private function MapperGeneratorProvider()
    {
        $this->mockFactory();

        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();
        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);
        $mockTwigGenerator = new \Mock\Builder\TwigGenerator();

        return new Builder\MapperGenerator($mockMapBuilder, $mockTwigGenerator);
    }
}