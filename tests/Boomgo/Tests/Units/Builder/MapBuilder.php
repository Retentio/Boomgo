<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Tests\Units\Builder;

use Boomgo\Tests\Units\Test;
use Boomgo\Builder;

/**
 * Builder tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 * @author David Guyon <dguyon@gmail.com>
 */
class MapBuilder extends Test
{
    public function test__construct()
    {
        // Should define the parser and the formatter
        $builder = $this->builderProvider();
        $this->assert
            ->object($builder->getParser())
                ->isInstanceOf('\\Mock\\Parser\\Parser')
            ->object($builder->getFormatter())
                ->isInstanceOf('\\Mock\\Formatter\\Formatter')
            ->string($builder->getMapClassName())
                ->isEqualTo('Boomgo\\Builder\\Map')
            ->string($builder->getDefinitionClassName())
                ->isEqualTo('Boomgo\\Builder\\Definition');
    }

    public function testGetParser()
    {
        // Should return the parser
        $builder = $this->builderProvider();
        $this->assert
            ->object($builder->getParser())
                ->isInstanceOf('\\Mock\\Parser\\Parser');
    }

    public function testGetFormatter()
    {
        // Should return the formatter
        $builder =  $this->builderProvider();
        $this->assert
            ->object($builder->getFormatter())
                ->isInstanceOf('\\Mock\\Formatter\\Formatter');
    }

    public function testMutatorsAndAccessors()
    {
        $mapbuilder = $this->builderProvider();

        $mapbuilder->setMapClassName('My\\New\\Map');
        $this->assert()
            ->string($mapbuilder->getMapClassName())
                ->isEqualTo('My\\New\\Map');

        $mapbuilder->setDefinitionClassName('My\\New\\Definition');
        $this->assert()
            ->string($mapbuilder->getDefinitionClassName())
                ->isEqualTo('My\\New\\Definition');
    }

    public function testBuild()
    {
        $fixtureDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture';
        $annotedDir = $fixtureDir.DIRECTORY_SEPARATOR.'Annoted';

        // Should  build Maps for an array of files
        $builder = $this->builderProvider();
        $processed = $builder->build(array($annotedDir.DIRECTORY_SEPARATOR.'Document.php', $annotedDir.DIRECTORY_SEPARATOR.'DocumentEmbed.php'));
        $this->assert
            ->array($processed)
                ->hasSize(2)
            ->object($processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document'])
                ->isInstanceOf('\\Boomgo\\Builder\\Map')
            ->object($processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\DocumentEmbed'])
                ->isInstanceOf('\\Boomgo\\Builder\\Map')
            ->array($processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document']->getDefinitions())
                ->hasSize(5)
                ->hasKeys(array('id', 'string', 'array', 'document', 'collection'))
            ->array($processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\DocumentEmbed']->getDefinitions())
                ->hasSize(2)
                ->hasKeys(array('string', 'array'));

        // Should throw exception on invalid metadata
        $this->mockClass('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'InvalidParser');
        $invalidMetadata =  array(
                'class' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document',
                'definitions' => array(
                    array(),
                    array('attribute' => 'string', 'type' => 'string')));

        $mockParser = new \Mock\Parser\InvalidParser;
        $mockParser->getMockController()->supports = function() { return true; };
        $mockParser->getMockController()->getExtension = function() { return 'php'; };
        $mockParser->getMockController()->parse = function($file) use ($invalidMetadata) { return $invalidMetadata; };

        $mockFormatter = new \Mock\Formatter\Formatter;

        $builder = new Builder\MapBuilder($mockParser, $mockFormatter);
        $this->assert
            ->exception(function() use ($builder) {
                $builder->build(array(__FILE__)); // random we do not care since parser is a mock
            })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Invalid metadata should provide an attribute or a key');

        // Should guess the attribute if only a key is provided
        $keyMetadata =  array(
                'class' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document',
                'definitions' => array(
                    array('key' => 'STRING', 'type' => 'string')));

        $mockParser = new \Mock\Parser\InvalidParser;
        $mockParser->getMockController()->supports = function() { return true; };
        $mockParser->getMockController()->getExtension = function() { return 'php'; };
        $mockParser->getMockController()->parse = function($file) use ($keyMetadata) { return $keyMetadata; };

        $mockFormatter = new \Mock\Formatter\Formatter;
        $mockFormatter->getMockController()->toPhpAttribute = function($string) { return strtolower($string); };
        $mockFormatter->getMockController()->toMongoKey = function($string) { return strtoupper($string); };
        $mockFormatter->getMockController()->getPhpAccessor = function($string) { return 'get'.ucfirst($string); };
        $mockFormatter->getMockController()->getPhpMutator = function($string) { return 'set'.ucfirst($string); };

        $builder = new Builder\MapBuilder($mockParser, $mockFormatter);
        $processed = $builder->build(array(__FILE__)); // random we do not care since parser is a mock
        $this->assert
            ->array($processed)
                ->hasSize(1)
            ->object($processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document'])
                ->isInstanceOf('\\Boomgo\\Builder\\Map');

        $definitions = $processed['\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document']->getDefinitions();
        $this->assert
            ->object($definitions['string'])
            ->string($definitions['string']->getAttribute())
                ->isEqualTo('string');

    }

    private function builderprovider()
    {
        if (!class_exists('\\Mock\\Parser\\Parser') && !class_exists('\\Mock\\Formatter\\Formatter')) {
            $this->mockClass('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
            $this->mockClass('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
        }

        $fixtureMetadata = $this->metadataProvider();

        $mockParser = new \Mock\Parser\Parser;
        $mockParser->getMockController()->supports = function() { return true; };
        $mockParser->getMockController()->getExtension = function() { return 'php'; };
        $mockParser->getMockController()->parse = function($file) use ($fixtureMetadata) { return $fixtureMetadata[$file]; };

        $mockFormatter = new \Mock\Formatter\Formatter;
        $mockFormatter->getMockController()->toPhpAttribute = function($string) { return strtolower($string); };
        $mockFormatter->getMockController()->toMongoKey = function($string) { return strtoupper($string); };
        $mockFormatter->getMockController()->getPhpAccessor = function($string) { return 'get'.ucfirst($string); };
        $mockFormatter->getMockController()->getPhpMutator = function($string) { return 'set'.ucfirst($string); };

        return new Builder\MapBuilder($mockParser, $mockFormatter);
    }

    private function metadataProvider()
    {
        $document = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'Annoted'.DIRECTORY_SEPARATOR.'Document.php';
        $embed = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'Annoted'.DIRECTORY_SEPARATOR.'DocumentEmbed.php';
        $another = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'AnotherAnnoted'.DIRECTORY_SEPARATOR.'Document.php';

        return array(
            $document => array(
                'class' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\Document',
                'definitions' => array(
                    array('attribute' => 'id', 'type' => 'string'),
                    array('attribute' => 'string', 'type' => 'string'),
                    array('attribute' => 'array', 'type' => 'array'),
                    array('attribute' => 'document', 'type' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\DocumentEmbed'),
                    array('attribute' => 'collection', 'type' => 'array', 'mappedClass' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\DocumentEmbed' ))),
            $embed => array(
                'class' => '\\Boomgo\\Tests\\Units\\Fixture\\Annoted\\DocumentEmbed',
                'definitions' => array(
                    array('attribute' => 'string', 'type' => 'string'),
                    array('attribute' => 'array', 'type' => 'array'))),
            $another => array(
                'class' => '\\Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted\\Document',
                'definitions' => array(
                    array('attribute' => 'id', 'type' => 'string'),
                    array('attribute' => 'string', 'type' => 'string'),
                    array('attribute' => 'array', 'type' => 'array'),
                    array('attribute' => 'document', 'type' => '\\Boomgo\\Tests\\Units\\Fixture\\AnotherAnnoted\\Document')))
            );
    }
}