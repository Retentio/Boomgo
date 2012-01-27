<?php

namespace Boomgo\Formatter;

interface FormatterInterface
{   
    /**
     * Format a php attribute name to a mongo key
     * 
     * @param  string $mongoKey
     * @return string
     */
    public function toPhpAttribute($mongoKey);
    
    /**
     * Format a mongo key to a php attribute
     * 
     * @param  string $toPhpAttribute
     * @return string
     */
    public function toMongoKey($toPhpAttribute);
}
