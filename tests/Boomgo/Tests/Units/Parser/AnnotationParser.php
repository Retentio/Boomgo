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

namespace Boomgo\Tests\Units\Parser;

use Boomgo\Tests\Units\Test;
use Boomgo\Parser;

/**
 * AnnotationParser tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class AnnotationParser extends Test
{
    public function test__construct()
    {
        // Should be able to define the local and global annotation though the constructor
        $parser = new Parser\AnnotationParser('@Boomgo', '@MyHypeAnnot');

        $this->assert
            ->string($parser->getLocalAnnotation())
                ->isIdenticalTo('@MyHypeAnnot')
            ->string($parser->getGlobalAnnotation())
                ->isIdenticalTo('@Boomgo');
    }

    public function testSetGetGlobalAnnotation()
    {
        $parser = new Parser\AnnotationParser();

        // Should set and get global annotation
        $parser->setGlobalAnnotation('@MyHypeAnnot');

        $this->assert
            ->string($parser->getGlobalAnnotation())
                ->isIdenticalTo('@MyHypeAnnot');

        // Should throw exception on invalid annotation
        $this->assert
            ->exception(function() use ($parser) {
                $parser->setGlobalAnnotation('invalid');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Boomgo annotation tag should start with "@" character');
    }

    public function testSetGetLocalAnnotation()
    {
        $parser = new Parser\AnnotationParser();

        // Should set and get local annotation
        $parser->setLocalAnnotation('@MyHypeAnnot');

        $this->assert
            ->string($parser->getLocalAnnotation())
                ->isIdenticalTo('@MyHypeAnnot');

        // Should throw exception on invalid annotation
        $this->assert
            ->exception(function() use ($parser) {
                $parser->setLocalAnnotation('invalid');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Boomgo annotation tag should start with "@" character');
    }

    public function testGetExtension()
    {
        // Should return php as supported extension
        $parser = new Parser\AnnotationParser();
        $this->assert
            ->string($parser->getExtension())
                ->isEqualTo('php');
    }

    public function testSupports()
    {
        // Should return true for a php file
        $parser = new Parser\AnnotationParser();
        $this->assert
            ->boolean($parser->supports(__FILE__))
                ->isTrue();

        // Should return false for a non php file
        $unsupported = __DIR__.DIRECTORY_SEPARATOR.'unsupported';
        touch($unsupported);

        $this->assert
            ->boolean($parser->supports($unsupported))
                ->isFalse();

        unlink($unsupported);
    }

    public function testParse()
    {
        // Should throw exception if no namespace is defined in the file
        $parser = new Parser\AnnotationParser();
        $this->assert
            ->exception(function() use ($parser) {
                $parser->parse(__DIR__.'/../Fixture/NoNamespace.php');
            })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Unable to find namespace or class declaration');

        // Should throw exception if Boomgo local annotation is not unique per property
        $parser = new Parser\AnnotationParser();
        $this->assert
            ->exception(function() use ($parser) {
                $parser->parse(__DIR__.'/../Fixture/AnnotationInvalidBoomgo.php');
            })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Boomgo annotation tag should occur only once for "Boomgo\Tests\Units\Fixture\AnnotationInvalidBoomgo->invalidAnnotation"');

        // Should throw exception if a Boomgo property contains more than on @var tag
        $parser = new Parser\AnnotationParser();
        $this->assert
            ->exception(function() use ($parser) {
                $parser->parse(__DIR__.'/../Fixture/AnnotationInvalidVar.php');
            })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('"@var" tag is not unique for "Boomgo\Tests\Units\Fixture\AnnotationInvalidVar->invalidVar"');

        // Should parse an annoted class
        $parser = new Parser\AnnotationParser();

        $metadata = $parser->parse(__DIR__.'/../Fixture/Annotation.php');
        $this->assert
            ->array($metadata)
                ->hasSize(2)
                ->hasKeys(array('class', 'definitions'))
            ->string($metadata['class'])
                ->isIdenticalTo('Boomgo\\Tests\\Units\\Fixture\\Annotation')
            ->array($metadata['definitions'])
                ->hasSize(7)
                ->hasKeys(array('novar', 'type', 'typeDescription',  'namespace', 'typeNamespace', 'typeManyNamespace', 'typeInvalidNamespace'))
            ->array($metadata['definitions']['novar'])
                ->hasSize(1)
                ->isIdenticalTo(array('attribute' => 'novar'))
            ->array($metadata['definitions']['type'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'type', 'type' => 'type'))
            ->array($metadata['definitions']['typeDescription'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'typeDescription', 'type' => 'type'))
            ->array($metadata['definitions']['namespace'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'namespace', 'type' => 'Type\\Is\\Namespace\\Object'))
            ->array($metadata['definitions']['typeNamespace'])
                ->hasSize(3)
                ->isIdenticalTo(array('attribute' => 'typeNamespace', 'type' => 'type', 'mappedClass' => 'Valid\\Namespace\\Object'))
            ->array($metadata['definitions']['typeManyNamespace'])
                ->hasSize(3)
                ->isIdenticalTo(array('attribute' => 'typeManyNamespace', 'type' => 'type', 'mappedClass' => 'First\\Namespace\\Object Second\\Namespace\\Object'))
            ->array($metadata['definitions']['typeInvalidNamespace'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'typeInvalidNamespace', 'type' => 'type'));
    }
}