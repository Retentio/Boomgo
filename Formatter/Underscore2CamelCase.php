<?php

namespace Boomgo\Formatter;

/**
 * Formatter for PHP CamelCase attribute to Mongo key underscore   
 */
class Underscore2CamelCase implements FormatterInterface
{
    /**
     * Convert underscored string to (lower) camelCase
     * 
     * @example my_great_key -> myGreatKey
     * 
     * @param  string $mongoKey An underscored string
     * @return string
     */
    public function toPhpAttribute($mongoKey)
    {
        $words = explode('_', strtolower($mongoKey));
        
        $camelized = '';
        
        foreach ($words as $word) {
            if (strpos($word,'_') === false) {
                $camelized .= ucfirst(trim($word));
            }
        }

        return lcfirst($camelized);
    }

    /**
     * Convert camelCase string to underscore
     * 
     * @example myGreatKey|MyGreatKey -> my_great_key
     * 
     * @param  string $phpAttribute A camelCase string
     * @return string
     */
    public function toMongoKey($phpAttribute)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $phpAttribute));
    }
}