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

namespace Boomgo\Tests\Units\Map;

use Boomgo\Tests\Units\Test;
use Boomgo\Map;

/**
 * Builder tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Builder extends Test
{
    public function metadataProvider()
    {
        return array ('class' => 'Test\FQDN',
            'definitions' => array(
                'mixed' => array ('attribute' => 'mixed'),
                'string' => array ('attribute' => 'string', 'type' => 'string'),
                'number' => array ('attribute' => 'number', 'type' => 'number'),
                'document' => array ('attribute' => 'document', 'type' => 'User\\Namespace\\Object'),
                'collection' => array ('attribute' => 'collection', 'type' => 'array', 'mappedClass' => 'Valid\\Namespace\\Object')
        ));
    }

    public function testBuild()
    {
        //$this->mock('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
        $this->mock('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
        $this->mock('Boomgo\\Cache\\CacheInterface', '\\Mock\\Cache', 'Cache');
        //$mockParser = new \Mock\Parser\Parser;
        $mockParser = new \Boomgo\Parser\AnnotationParser;
        $mockCache = new \Mock\Cache\Cache;
        $mockFormatter = new \Mock\Formatter\Formatter;
        $mockFormatter->getMockController()->toPhpAttribute = function($string) { return $string; };

        $builder = new Map\Builder($mockParser, $mockFormatter, $mockCache);
        $builder->build(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'AnnotedDocument.php');
    }

    // public function testBuildMap()
    // {
    //     $this->mock('Boomgo\\Parser\\ParserInterface', '\\Mock\\Parser', 'Parser');
    //     $this->mock('Boomgo\\Formatter\\FormatterInterface', '\\Mock\\Formatter', 'Formatter');
    //     $mockParser = new \Mock\Parser\Parser;
    //     $mockFormatter = new \Mock\Formatter\Formatter;
    //     $mockFormatter->getMockController()->toPhpAttribute = function($string) { return $string; };

    //     $builder = new Map\Builder($mockParser, $mockFormatter);
    //     $map = $builder->buildMap($this->metadataProvider());
    // }
}