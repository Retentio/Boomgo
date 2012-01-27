<?php

namespace Boomgo\tests\units\Mock;

use Boomgo\Formatter\FormatterInterface;

/**
 * Dummy formatter 
 */
class Formatter implements FormatterInterface
{
    public function toPhpAttribute($mongoKey)
    {
        return $mongoKey;
    }

    public function toMongoKey($phpAttribute)
    {
        return $phpAttribute;
    }
}
