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
 * CamelCaseFormatter tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class CamelCaseFormatter extends Test
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
        $formatter = new Formatter\CamelCaseFormatter();

        // Should return the exact provided string
        $this->assert
            ->string($formatter->toMongoKey('phpAttribute'))
                ->isEqualTo('phpAttribute');

        // Should handle _id MongoDB exception and add the underscore
        $this->assert
            ->string($formatter->toMongoKey('id'))
                ->isEqualTo('_id');
    }

    public function testToPhpAttribute()
    {
        $formatter = new Formatter\CamelCaseFormatter();

        // Should return the exact provided string
        $this->assert
            ->string($formatter->toPhpAttribute('mongoKey'))
            ->isEqualTo('mongoKey');

        // Should handle _id MongoDB exception and remove the underscore
        $this->assert
            ->string($formatter->toPhpAttribute('_id'))
                ->isEqualTo('id');
    }

    public function testGetPhpAccessor()
    {
        $formatter = new Formatter\CamelCaseFormatter();

        // Should prefixes with get and capitalizes first attribute letter
        $this->assert
            ->string($formatter->getPhpAccessor('mongoKey', 'mixed'))
                ->isEqualTo('getMongoKey');

        // Should prefixes with is and capitalizes first attribute letter for a boolean type
        $this->assert
            ->string($formatter->getPhpAccessor('mongoKey', 'boolean'))
                ->isEqualTo('isMongoKey')
            ->string($formatter->getPhpAccessor('mongoKey', 'bool'))
                ->isEqualTo('isMongoKey');
    }

    public function testGetPhpMutator()
    {
        $formatter = new Formatter\CamelCaseFormatter();

        // Should prefixes with set and capitalize first attribute letter
        $this->assert
            ->string($formatter->getPhpMutator('mongoKey'))
            ->isEqualTo('setMongoKey');
    }
}