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
 * CamelCaseFormatter
 *
 * Formatter for Mongo key camelCase & PHP camelCase attribute.
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class CamelCaseFormatter implements FormatterInterface
{
    /**
     * Format a mongo key to a php attribute
     *
     * @param string $string
     *
     * @return string $string
     */
    public function toMongoKey($string)
    {
        return ($string == 'id') ? '_id' : $string;
    }

    /**
     * Format a php attribute to a mongo key
     *
     * @param string $string
     *
     * @return string $string
     */
    public function toPhpAttribute($string)
    {
        return ($string == '_id') ? 'id' : $string;
    }

    /**
     * Return a php accessor for a php attribute
     *
     * @param string $string A mongo key or a php attribute
     *
     * @return string
     */
    public function getPhpAccessor($string, $type = 'mixed')
    {
        $prefix = (($type == 'bool' || $type == 'boolean') ? 'is' : 'get');

        return $prefix.ucfirst($string);
    }

    /**
     * Return a php mutator for a php attribute
     *
     * @param string $string A mongo key or a php attribute
     *
     * @return string
     */
    public function getPhpMutator($string, $type = 'mixed')
    {
        return 'set'.ucfirst($string);
    }
}