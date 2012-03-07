<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Formatter;

/**
 * Underscore2CamelFormatter
 *
 * Formatter for Mongo key underscore & PHP camelCase attribute.
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Underscore2CamelFormatter implements FormatterInterface
{
    /**
     * Return an underscored mongo key from a php attribute
     *
     * Handle _id exception since mongoDB use underscored identifier
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
     * Return a camelCase php attribute from a underscored mongo key
     *
     * @param  string $mongoKey An underscored string
     * @return string
     */
    public function toPhpAttribute($mongoKey)
    {
        return $this->camelize($mongoKey, true);
    }

    /**
     * Return a php accessor for a mongo key or a php attribute
     *
     * @param  string  $string    A mongo key or a php attribute
     * @param  boolean $fromMongo True if an underscored mongo key is provided, false: for a camelCase php attribute
     * @return string
     */
    public function getPhpAccessor($string, $type = 'mixed', $fromMongo = true)
    {
        $prefix = (($type == 'bool' || $type == 'boolean') ? 'is' : 'get');
        return $prefix.(($fromMongo) ? $this->camelize($string, false) : ucfirst($string));
    }

    /**
     * Return a php mutator for a mongo key or a php attribute
     *
     * @param  string  $string    A mongo key or a php attribute
     * @param  boolean $fromMongo True if an underscored mongo key is provided, false: for a camelCase php attribute
     * @return string
     */
    public function getPhpMutator($string, $type = 'mixed', $fromMongo = true)
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