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
     * @param string $phpAttribute A camelCase string
     *
     * @return string
     */
    public function toMongoKey($phpAttribute)
    {
        $underscored =  $this->underscore($phpAttribute);

        return ($phpAttribute == 'id') ? '_id' : $underscored ;
    }

    /**
     * Return a camelCase php attribute from a underscored mongo key
     *
     * @param string $mongoKey An underscored string
     *
     * @return string
     */
    public function toPhpAttribute($mongoKey)
    {
        // will camelize in lower case (so it handles the _id exception)
        return $this->camelize($mongoKey);
    }

    /**
     * Return a php accessor for a php attribute
     *
     * @param string  $string  A php attribute
     * @param string  $type    The php type
     *
     * @return string
     */
    public function getPhpAccessor($string, $type = 'mixed')
    {
        $prefix = (($type ==='bool' || $type === 'boolean') ? 'is' : 'get');

        return $prefix.ucfirst($string);
    }

    /**
     * Return a php mutator for a php attribute
     *
     * @param string  $string A php attribute
     *
     * @return string
     */
    public function getPhpMutator($string)
    {
        return 'set'.ucfirst($string);
    }

    /**
     * Convert underscored string to lower|upper camelCase
     *
     * @param string $string An underscored string
     * @param bool   $lower
     *
     * @return string
     *
     * @example my_great_key -> myGreatKey|MyGreatKey
     */
    private function camelize($string, $lower = true)
    {
        $words = explode('_', strtolower($string));

        $camelized = '';

        foreach ($words as $word) {
            if (strpos($word, '_') === false) {
                $camelized .= ucfirst(trim($word));
            }
        }

        return ($lower) ? lcfirst($camelized) : $camelized;
    }

    /**
     * Convert a camelCase string to an underscore string
     *
     * @param string $string A camelCase string
     *
     * @return string
     *
     * @example myGreatKey|MyGreatKey -> my_great_key
     */
    private function underscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}