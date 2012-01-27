<?php

namespace Boomgo\Formatter;

class TransparentFormatter implements FormatterInterface 
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