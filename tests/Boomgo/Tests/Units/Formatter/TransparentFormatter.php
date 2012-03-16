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

namespace Boomgo\Tests\Units\Formatter;

use Boomgo\Tests\Units\Test;
use Boomgo\Formatter;

/**
 * TransparentFormatter tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class TransparentFormatter extends Test
{
    public function test__construct()
    {
        // Should implement FormatterInterface
        $this->assert
            ->class('Boomgo\Formatter\TransparentFormatter')
            ->hasInterface('Boomgo\Formatter\FormatterInterface');
    }

    public function testToMongoKey()
    {
        $formatter = new Formatter\TransparentFormatter();

        // Should return the exact provided string
        $this->assert
            ->string($formatter->toMongoKey('FreeStyle php_AttrIbute'))
            ->isEqualTo('FreeStyle php_AttrIbute');
    }

    public function testToPhpAttribute()
    {
        $formatter = new Formatter\TransparentFormatter();

        // Should return the exact provided string
        $this->assert
            ->string($formatter->toPhpAttribute('FreeStyle MongoKey__'))
            ->isEqualTo('FreeStyle MongoKey__');

    }

    public function testGetPhpAccessor()
    {
        $formatter = new Formatter\TransparentFormatter();

        // Should always prefix the provided string with get or is
        $this->assert
            ->string($formatter->getPhpAccessor('FreeStyle MongoKey__'))
                ->isEqualTo('getFreeStyle MongoKey__')
            ->string($formatter->getPhpAccessor('FreeStyle MongoKey__', 'mixed'))
                ->isEqualTo('getFreeStyle MongoKey__')
            ->string($formatter->getPhpAccessor('FreeStyle MongoKey__', 'bool'))
                ->isEqualTo('isFreeStyle MongoKey__')
            ->string($formatter->getPhpAccessor('FreeStyle MongoKey__', 'boolean'))
                ->isEqualTo('isFreeStyle MongoKey__');

    }

    public function testGetPhpMutator()
    {
        $formatter = new Formatter\TransparentFormatter();

        // Should always prefix the provided string with set
        $this->assert
            ->string($formatter->getPhpMutator('FreeStyle MongoKey__'))
                ->isEqualTo('setFreeStyle MongoKey__')
            ->string($formatter->getPhpMutator('FreeStyle MongoKey__', 'mixed'))
                ->isEqualTo('setFreeStyle MongoKey__');
    }
}