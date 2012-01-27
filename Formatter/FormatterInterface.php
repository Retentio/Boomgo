<?php

namespace Boomgo\Formatter;

interface FormatterInterface
{   
    /**
     * Format a php attribute from a mongo key
     * 
     * @param  string $mongoKey
     * @return string
     */
    public function toPhpAttribute($mongoKey);
    
    /**
     * Format a mongo key from a php attribute
     * 
     * @param  string $toPhpAttribute
     * @return string
     */
    public function toMongoKey($phpAttribute);
}
