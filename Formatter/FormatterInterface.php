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

    /**
     * Get a php accessor name from a mongo key or a php attribute
     * 
     * @param  string  $string    The php attribute or the mongo key
     * @param  boolean $fromMongo True for a mongo key string, false for a php attribute
     * @return string
     */
    public function getPhpAccessor($string, $fromMongo = true);

    /**
     * Get a php mutator name from a mongo key or a php attribute
     * 
     * @param  string  $string    The php attribute or the mongo key
     * @param  boolean $fromMongo True for a mongo key string, false for a php attribute
     * @return string
     */
    public function getPhpMutator($string, $fromMongo = true);
}
