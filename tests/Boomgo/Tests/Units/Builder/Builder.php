<?php

/**
 * This file is part of the Boomgo PHP ODM.
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
use Boomgo\Builder as Src;

/**
 * Builder tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Builder extends Test
{
    public function testBuild()
    {
        $this->mock('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
        $this->mock('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
        $this->mock('Boomgo\\Writer\\WriterInterface', '\\Mock\\Writer', 'Writer');

        $fixtureMetadata = $this->metadataProvider();

        $mockParser = new \Mock\Parser\Parser;
        $mockParser->getMockController()->supports = function() { return true; };
        $mockParser->getMockController()->parse = function($file) use ($fixtureMetadata) { return ($file == __FILE__) ? $fixtureMetadata['\\Fixture\\Class'] : $fixtureMetadata['\\Fixture\\Embed\\Class']; };

        $mockFormatter = new \Mock\Formatter\Formatter;
        $mockFormatter->getMockController()->toPhpAttribute = function($string) { return $string; };
        $mockFormatter->getMockController()->toMongoKey = function($string) { return strtoupper($string); };
        $mockFormatter->getMockController()->getPhpAccessor = function($string) { return 'get'.ucfirst($string); };
        $mockFormatter->getMockController()->getPhpMutator = function($string) { return 'set'.ucfirst($string); };

        $mockWriter = new \Mock\Writer\Writer;


        $builder = new Src\Builder($mockParser, $mockFormatter, $mockWriter);

        $this->assert
            ->array($builder->build(array(__FILE__, __DIR__.DIRECTORY_SEPARATOR.'Map.php')))
                ->hasSize(2)
            ->mock($mockWriter)
                ->call('write')
                    ->exactly(2);

        $processed = $builder->build(array(__FILE__, __DIR__.DIRECTORY_SEPARATOR.'Map.php'));
        $map = $processed['\\Fixture\\Class'];
        $this->assert
            ->object($map)
                ->isInstanceOf('\\Boomgo\\Builder\\Map');

        $definition = $map->getDefinition('document');
        $this->assert
            ->object($definition)
                ->isInstanceOf('\\Boomgo\\Builder\\Definition');

        $definition = $map->getDefinition('collection');
        $this->assert
            ->object($definition)
                ->isInstanceOf('\\Boomgo\\Builder\\Definition');

        $dependencies = $map->getDependencies();
        $this->assert
            ->array($dependencies)
                ->hasSize(1)
                ->hasKey('\\Fixture\\Embed\\Class');

        $dependency = $map->getDependency('\\Fixture\\Embed\\Class');
        $this->assert
            ->object($dependency)
                ->isInstanceOf('\\Boomgo\\Builder\\Map');


        $definition = $dependency->getDefinition('string');
        $this->assert
            ->object($definition)
                ->isInstanceOf('\\Boomgo\\Builder\\Definition');
    }

    private function metadataProvider()
    {
        return array(
            '\\Fixture\\Class' => array(
                'class' => '\\Fixture\\Class',
                'definitions' => array(
                    array('attribute' => 'string', 'type' => 'string'),
                    array('attribute' => 'array', 'type' => 'array'),
                    array('attribute' => 'document', 'type' => '\\Fixture\\Embed\\Class'),
                    array('attribute' => 'collection', 'type' => 'array', 'mappedClass' => '\\Fixture\\Embed\\Class' ))),
            '\\Fixture\\Embed\\Class' => array(
                'class' => '\\Fixture\\Embed\\Class',
                'definitions' => array(
                    array('attribute' => 'string', 'type' => 'string'),
                    array('attribute' => 'array', 'type' => 'array'))));
    }
}