<?php

namespace Boomgo\Formatter;

/**
 * Formatter for PHP CamelCase attribute to Mongo key underscore   
 */
class Underscore2CamelFormatter implements FormatterInterface
{

    /**
     * Return an underscored mongo key
     * 
     * @param  string $phpAttribute A camelCase string
     * @return string
     */
    public function toMongoKey($phpAttribute)
    {
        return $this->underscore($phpAttribute);
    }

    /**
     * Return a camelCase php attribute 
     * 
     * @param  string $mongoKey An underscored string
     * @return string
     */
    public function toPhpAttribute($mongoKey)
    {
        return $this->camelize($mongoKey, true);
    }

    /**
     * Return a php mutator
     * 
     * @param  string $mongoKey
     * @return string
     */
    public function toPhpMutator($mongoKey)
    {
        return 'set'.$this->camelize($string, false);
    }

    /**
     * Convert underscored string to lower|upper camelCase
     * 
     * @example my_great_key -> myGreatKey|MyGreatKey
     * 
     * @param  string $mongoKey An underscored string
     * @param  bool   $lower
     * @return string
     */
    private function camelize($string, $lower = false)
    {
        $words = explode('_', strtolower($string));
        
        $camelized = '';
        
        foreach ($words as $word) {
            if (strpos($word,'_') === false) {
                $camelized .= ucfirst(trim($word));
            }
        }

        return ($lower) ? lcfirst($camelized) : $camelized;
    }

    /**
     * Convert a camelCase string to an underscore string
     * 
     * @example my_great_key -> myGreatKey
     * 
     * @param  string $string A camelCase string
     * @return string
     */
    private function underscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}