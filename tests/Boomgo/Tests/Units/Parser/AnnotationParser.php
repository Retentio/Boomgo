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
        // Should be able to define the annotation though the constructor
        $parser = new Parser\AnnotationParser('@MyHypeAnnot');

        $this->assert
            ->string($parser->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');
    }

    public function testSetGetAnnotation()
    {
        $parser = new Parser\AnnotationParser();

        // Should set and get annotation
        $parser->setAnnotation('@MyHypeAnnot');

        $this->assert
            ->string($parser->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');

        // Should throw exception on invalid annotation
        $this->assert
            ->exception(function() use ($parser) {
                $parser->setAnnotation('invalid');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Boomgo annotation tag should start with "@" character');
    }

    public function testParse()
    {
        $parser = new Parser\AnnotationParser();

        $metadata = $parser->parse('Boomgo\\Tests\\Units\\Fixture\\Annotation');
        $this->assert
            ->array($metadata)
                ->hasSize(7)
                ->hasKeys(array('novar', 'type', 'typeDescription',  'namespace', 'typeNamespace', 'typeManyNamespace', 'typeInvalidNamespace'))
            ->array($metadata['novar'])
                ->hasSize(1)
                ->isIdenticalTo(array('attribute' => 'novar'))
            ->array($metadata['type'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'type', 'type' => 'type'))
            ->array($metadata['typeDescription'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'typeDescription', 'type' => 'type'))
            ->array($metadata['namespace'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'namespace', 'type' => 'Type\\Is\\Namespace\\Object'))
            ->array($metadata['typeNamespace'])
                ->hasSize(3)
                ->isIdenticalTo(array('attribute' => 'typeNamespace', 'type' => 'type', 'mappedClass' => 'Valid\\Namespace\\Object'))
            ->array($metadata['typeManyNamespace'])
                ->hasSize(3)
                ->isIdenticalTo(array('attribute' => 'typeManyNamespace', 'type' => 'type', 'mappedClass' => 'First\\Namespace\\Object Second\\Namespace\\Object'))
            ->array($metadata['typeInvalidNamespace'])
                ->hasSize(2)
                ->isIdenticalTo(array('attribute' => 'typeInvalidNamespace', 'type' => 'type'));
    }
}