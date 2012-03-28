<?php

namespace Boomgo\Tests\Units\Builder\Generator;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder;
use Boomgo\Builder\Generator\MapperGenerator as BaseMapperGenerator;

class MapperGenerator extends Test
{
    public $generator;

    public function beforeTestMethod($method)
    {
        $this->mockClass('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
        $this->mockClass('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
        $this->mockClass('Boomgo\\Builder\\Map', '\\Mock\\Builder', 'Map');
        $this->mockClass('Boomgo\\Builder\\MapBuilder', '\\Mock\\Builder', 'MapBuilder');
        $this->mockClass('TwigGenerator\\Builder\\Generator', '\\Mock\\Builder', 'TwigGenerator');

        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();

        $mockMap = new \Mock\Builder\Map('Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted\\Document');
        $mockMap->getMockController()->getClassName = function() { return 'Document'; };
        $mockMap->getMockController()->getNamespace = function() { return 'Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted'; };

        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);
        $mockMapBuilder->getMockController()->build = function() use ($mockMap) { return array($mockMap); };

        // Avoid constructor call for TwigGenerator with creating directory
        $controllerTwigGenerator = new \mageekguy\atoum\mock\controller();
        $controllerTwigGenerator->__construct = function() {};
        $controllerTwigGenerator->writeOndisk = function() {};
        $mockTwigGenerator = new \Mock\Builder\TwigGenerator($controllerTwigGenerator);

        $this->generator = new BaseMapperGenerator($mockMapBuilder, $mockTwigGenerator);
    }

    public function afterTestMethod($method)
    {
        $this->generator = null;
    }

    public function testGenerate()
    {
        $generator = $this->generator;

        $this->assert
            ->exception(function () use ($generator) {
                $generator->generate(array(__DIR__.'/../../Fixture/AnotherAnnoted.php'), 'Document', 'Mapper', '/Fixture');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Boomgo support only PSR-O structure, your namespace "Document" doesn\'t reflect your directory structure "/Fixture"');

        $this->assert
            ->exception(function () use ($generator) {
                $generator->generate(array(__DIR__.'/../../Fixture/AnotherAnnoted'), 'Document', 'Mapper', __DIR__.'/../../Document/');
            })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('The Document map "\Boomgo\Tests\Units\Fixture\AnotherAnnoted\Document" doesn\'t include the document base namespace "Document"');

        $this->assert
            ->boolean($generator->generate(array(__DIR__.'/../../Fixture/AnotherAnnoted/Document.php'), 'Fixture', 'Mapper', __DIR__.'/../Fixture/'))
                ->isTrue();
    }

    /**
     * @todo test generated code
     */
    // public function testFunctionalGenerate()
    // {
    //     $formatter = new \Boomgo\Formatter\CamelCaseFormatter();
    //     $parser = new \Boomgo\Parser\AnnotationParser();
    //     $mapBuilder = new \Boomgo\Builder\MapBuilder($parser, $formatter);
    //     $twigGenerator = new \TwigGenerator\Builder\Generator();
    //     $generator = new Builder\Generator\MapperGenerator($mapBuilder, $twigGenerator, array('namespace' => array('models' => 'Fixture', 'mappers' => 'Mapper')));
    //     $generator->generate(array(__DIR__.'/../Fixture/Annoted/Document.php', __DIR__.'/../Fixture/Annoted/DocumentEmbed.php'));
    // }
}