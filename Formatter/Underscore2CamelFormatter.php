<?php

namespace Boomgo\Formatter;

/**
 * Formatter for PHP CamelCase attribute to Mongo key underscore   
 */
class Underscore2CamelFormatter implements FormatterInterface
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
        return $this->camelize($mongoKey, true);
    }

    public function toPhpMutator($mongoKey)
    {
        return 'set'.$this->camelize($string, false);
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
        return $this->underscore($phpAttribute);
    }

    private function camelize($string, $lower = false)
    {
        $words = explode('_', strtolower($mongoKey));
        
        $camelized = '';
        
        foreach ($words as $word) {
            if (strpos($word,'_') === false) {
                $camelized .= ucfirst(trim($word));
            }
        }

        return ($lower) ? lcfirst($camelized) : $camelized;
    }

    private function underscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}