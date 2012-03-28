<?php

namespace Boomgo\Tests\Units\Builder\Generator;

use Boomgo\Tests\Units\Test;

class AbstractGenerator extends Test
{
    public $generator;

    public function beforeTestMethod($method)
    {
        $this->mockClass('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
        $this->mockClass('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
        $this->mockClass('Boomgo\\Builder\\MapBuilder', '\\Mock\\Builder', 'MapBuilder');
        $this->mockClass('TwigGenerator\\Builder\\Generator', '\\Mock\\Builder', 'TwigGenerator');
        $this->mockClass('Boomgo\\Builder\\Generator\\AbstractGenerator', '\\Mock\\Builder\\Generator', 'AbstractGenerator');

        $mockMapBuilder = $this->getMockMapBuilder();
        $mockTwigGenerator = $this->getMockTwigGenerator();

        $this->generator = new \Mock\Builder\Generator\AbstractGenerator($mockMapBuilder, $mockTwigGenerator);
    }

    public function afterTestMethod($method)
    {
        $this->generator = null;
    }

    /**
     * getMockMapBuilder
     * 
     * @return \Mock\Builder\MapBuilder
     */
    public function getMockMapBuilder()
    {
        $mockParser = new \Mock\Parser\Parser();
        $mockFormatter = new \Mock\Formatter\Formatter();
        $mockMapBuilder = new \Mock\Builder\MapBuilder($mockParser, $mockFormatter);

        return $mockMapBuilder;
    }

    /**
     * getMockTwigGenerator
     * 
     * @return \Mock\Builder\TwigGenerator
     */
    public function getMockTwigGenerator()
    {
        // Avoid constructor call for TwigGenerator with creating directory
        $controller = new \mageekguy\atoum\mock\controller();
        $controller->__construct = function() {};

        $mockTwigGenerator = new \Mock\Builder\TwigGenerator($controller);

        return $mockTwigGenerator;
    }

    public function testConstruct()
    {
        $mockMapBuilder = $this->getMockMapBuilder();
        $mockTwigGenerator = $this->getMockTwigGenerator();

        $generator = new \Mock\Builder\Generator\AbstractGenerator($mockMapBuilder, $mockTwigGenerator);

        $this->assert()
            ->object($this->generator)
                ->isInstanceOf('Boomgo\\Builder\\Generator\\AbstractGenerator');
    }

    public function testSetterGetter()
    {
        $anotherMockMapBuilder = $this->getMockMapBuilder();
        $this->generator->setMapBuilder($anotherMockMapBuilder);

        $this->assert()
            ->variable($this->generator->getMapBuilder())
                ->isIdenticalTo($anotherMockMapBuilder);

        $anotherMockTwigGenerator = $this->getMockTwigGenerator();
        $this->generator->setTwigGenerator($anotherMockTwigGenerator);

        $this->assert()
            ->variable($this->generator->getTwigGenerator())
                ->isIdenticalTo($anotherMockTwigGenerator);
    }

    public function testLoad()
    {
        // Closure doesn't accept $this context
        $generator = $this->generator;

        // Should throw an exception if argument is a string and not a valid file or directory
        $this->assert
            ->exception(function() use ($generator) {
                $generator->load('invalid path');
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an absolute directory or a file path or both in an array');

            // Should throw an exception if argument is an array and an element is not a valid file or directory
        $this->assert
            ->exception(function() use ($generator) {
                $generator->load(array(__FILE__, 'invalid path'));
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an absolute directory or a file path or both in an array');


        // Should return an array containing the realpath of a filename when providing a filename
        $this->assert
            ->array($generator->load(__DIR__.'/../../Fixture/Annoted/Document.php'))
                ->hasSize(1)
                ->strictlyContainsValues(array(realpath(__DIR__.'/../../Fixture/Annoted/Document.php')));

        // Should return an array containing many files realpath when providing a directory
        $this->assert
            ->array($generator->load(__DIR__.'/../../Fixture/Annoted'))
                ->hasSize(2)
                ->strictlyContainsValues(array(realpath(__DIR__.'/../../Fixture/Annoted/Document.php'), realpath(__DIR__.'/../../Fixture/Annoted/DocumentEmbed.php')));

        // Should return an array containing many files realpath when providing an array of directory
        $this->assert
            ->array($generator->load(array(__DIR__.'/../../Fixture/Annoted', __DIR__.'/../../Fixture/AnotherAnnoted')))
                ->hasSize(3)
                ->strictlyContainsValues(array(
                    realpath(__DIR__.'/../../Fixture/Annoted/Document.php'),
                    realpath(__DIR__.'/../../Fixture/Annoted/DocumentEmbed.php'),
                    realpath(__DIR__.'/../../Fixture/AnotherAnnoted/Document.php')));

        // Should return an array containing many files realpath when providing an array mixed with directory and file
        $this->assert
            ->array($generator->load(array(__DIR__.'/../../Fixture/Annoted', __DIR__.'/../../Fixture/AnotherAnnoted/Document.php')))
                ->hasSize(3)
                ->strictlyContainsValues(array(
                    realpath(__DIR__.'/../../Fixture/Annoted/Document.php'),
                    realpath(__DIR__.'/../../Fixture/Annoted/DocumentEmbed.php'),
                    realpath(__DIR__.'/../../Fixture/AnotherAnnoted/Document.php')));
    }
}