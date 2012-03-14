<?php

namespace Boomgo\Tests\Units;

use mageekguy\atoum\test as AtoumTest;

abstract class Test extends AtoumTest
{
    public function __construct(score $score = null, locale $locale = null, adapter $adapter = null)
    {
        $this->setTestNamespace('\\Tests\\Units\\');
        parent::__construct($score, $locale, $adapter);
    }
}