<?php

namespace Boomgo\Formatter;

/**
 * Formatter for PHP CamelCase attribute to Mongo key underscore
 */
class Underscore2CamelFormatter implements FormatterInterface
{

    /**
     * Return an underscored mongo key
     * Handle _id special mongoDB key
     *
     * @param  string $phpAttribute A camelCase string
     * @return string
     */
    public function toMongoKey($phpAttribute)
    {
        $underscored =  $this->underscore($phpAttribute);

        return ($phpAttribute == 'id') ? '_'.$underscored : $underscored ;
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
     * Return a php accessor for a mongo key
     *
     * @param  string  $string    A mongo key or a php attribute
     * @param  boolean $fromMongo True: string from mongo, false: string from php
     * @return string
     */
    public function getPhpAccessor($string, $fromMongo = true)
    {

        return 'get'.(($fromMongo) ? $this->camelize($string, false) : ucfirst($string));
    }

    /**
     * Return a php mutator for a mongo key
     *
     * @param  string  $string    A mongo key or a php attribute
     * @param  boolean $fromMongo True: string from mongo, false: string from php
     * @return string
     */
    public function getPhpMutator($string, $fromMongo = true)
    {
        return 'set'.(($fromMongo) ? $this->camelize($string, false) : ucfirst($string));
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