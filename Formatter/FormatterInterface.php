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
     * Get a php accessor name for a mongo key or a php attribute
     * 
     * @param  string  $string    
     * @param  boolean $fromMongo
     * @return string
     */
    public function getPhpAccessor($string, $fromMongo = true);

    /**
     * Get a php mutator name for a mongo key or a php attribute
     * 
     * @param  string  $string    
     * @param  boolean $fromMongo
     * @return string
     */
    public function getPhpMutator($string, $fromMongo = true);
}
