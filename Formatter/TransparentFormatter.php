<?php

namespace Boomgo\Formatter;

class TransparentFormatter implements FormatterInterface 
{
    public function toMongoKey($phpAttribute)
    {
        return $phpAttribute;
    }

    public function toPhpAttribute($mongoKey)
    {
        return $mongoKey;
    }

    public function getPhpAccessor($string, $fromMongo)
    {
        return 'get'.$string;
    }

    public function getPhpMutator($string, $fromMongo)
    {
        return 'set'.$string;
    }
}