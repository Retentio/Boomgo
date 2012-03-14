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
 * Underscore2CamelFormatter tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Underscore2CamelFormatter extends Test
{
    public function test__construct()
    {
        // Should implement FormatterInterface
        $this->assert
            ->class('Boomgo\Formatter\Underscore2CamelFormatter')
            ->hasInterface('Boomgo\Formatter\FormatterInterface');
    }

    public function testToPhpAttribute()
    {
        $formatter = new Formatter\Underscore2CamelFormatter();

        // Should (lower) camelize an underscored string
        $camelCase = $formatter->toPhpAttribute('hello_world_pol');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('helloWorldPol');

        // Should handle prefixed or suffixed string with underscore
        $camelCase = $formatter->toPhpAttribute('_world_');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('world');

        // Should handle double underscored string
        $camelCase = $formatter->toPhpAttribute('hello__world_');
        $this->assert
            ->string($camelCase)
            ->isEqualTo('helloWorld');
    }

    public function testToMongoAttribute()
    {
        $formatter = new Formatter\Underscore2CamelFormatter();

        // Should underscore an upper CamelCase string
        $underscore = $formatter->toMongoKey('HelloWorldPol');
        $this->assert
            ->string($underscore)
            ->isEqualTo('hello_world_pol');

        // Should underscore a lower CamelCase string
        $underscore = $formatter->toMongoKey('helloWorld');
        $this->assert
            ->string($underscore)
            ->isEqualTo('hello_world');


        // Should handle mongoDB identifier
        $underscore = $formatter->toMongoKey('id');
        $this->assert
            ->string($underscore)
            ->isEqualTo('_id');
    }

    public function testGetPhpAccessor()
    {
        $formatter = new Formatter\Underscore2CamelFormatter();

        // Should return a camelCase php accessor when implicitly providing an underscored mongo key
        $this->assert
            ->string($formatter->getPhpAccessor('underscored_mongo_key'))
            ->isEqualTo('getUnderscoredMongoKey');

        // Should return a camelCase php accessor when explicitly providing an underscored mongo key
        $this->assert
            ->string($formatter->getPhpAccessor('underscored_mongo_key', 'mixed', true))
            ->isEqualTo('getUnderscoredMongoKey');

        // Should return a camelCase php accessor when explicitly providing a lower camelCase php attribute
        $this->assert
            ->string($formatter->getPhpAccessor('underscoredMongoKey', 'mixed', false))
            ->isEqualTo('getUnderscoredMongoKey');

        // Should return a camelCase php accessor when explicitly providing an upper camelCase php attribute
        $this->assert
            ->string($formatter->getPhpAccessor('UnderscoredMongoKey', 'mixed', false))
            ->isEqualTo('getUnderscoredMongoKey');
    }

    public function testGetPhpMutator()
    {
        $formatter = new Formatter\Underscore2CamelFormatter();

        // Should return a camelCase php mutator when implicitly providing an underscored mongo key
        $this->assert
            ->string($formatter->getPhpMutator('underscored_mongo_key'))
            ->isEqualTo('setUnderscoredMongoKey');

        // Should return a camelCase php mutator when explicitly providing an underscored mongo key
        $this->assert
            ->string($formatter->getPhpMutator('underscored_mongo_key', true))
            ->isEqualTo('setUnderscoredMongoKey');

        // Should return a camelCase php mutator when explicitly providing a lower camelCase php attribute
        $this->assert
            ->string($formatter->getPhpMutator('underscoredMongoKey', false))
            ->isEqualTo('setUnderscoredMongoKey');

        // Should return a camelCase php mutator when explicitly providing an upper camelCase php attribute
        $this->assert
            ->string($formatter->getPhpMutator('UnderscoredMongoKey', false))
            ->isEqualTo('setUnderscoredMongoKey');
    }
}