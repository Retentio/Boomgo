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
        $this->mock('Boomgo\\Cache\\CacheInterface', '\\Mock\\Cache', 'Cache');
        //$mockParser = new \Mock\Parser\Parser;
        $mockParser = new \Boomgo\Parser\AnnotationParser;
        // $mockCache = new \Boomgo\Cache\FileCache();
        $mockCache = new \Mock\Cache\Cache;
        $mockFormatter = new \Mock\Formatter\Formatter;
        $mockFormatter->getMockController()->toPhpAttribute = function($string) { return $string; };
        $mockFormatter->getMockController()->toMongoKey = function($string) { return $string; };

        $builder = new Src\Builder($mockParser, $mockFormatter, $mockCache);

        $dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR;
        $processed = $builder->build(array($dir.'AnnotedDocument.php', $dir.'AnnotedDocumentEmbed.php'));
        $this->assert
            ->array($processed)
            ->hasSize(2);
    }
}