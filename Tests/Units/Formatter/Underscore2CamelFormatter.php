<?php

namespace Boomgo\tests\units\Formatter;

use Boomgo\Formatter;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';
include __DIR__.'/../../../Formatter/FormatterInterface.php';
include __DIR__.'/../../../Formatter/Underscore2CamelFormatter.php';

class Underscore2CamelFormatter extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        // Should implements FormatterInterface 
        $formatter = new Formatter\Underscore2CamelFormatter();

        $this->assert
            ->class('Boomgo\Formatter\Underscore2CamelFormatter')
            ->hasInterface('Boomgo\Formatter\FormatterInterface');

        $this->assert
            ->class($formatter)
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
    }
}