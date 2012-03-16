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
 * Formatter Interface
 *
 * Invoked to translate a mongo key name to a php attribute
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface FormatterInterface
{
    /**
     * Format a mongo key to a php attribute
     *
     * @param string $mongoKey
     *
     * @return string
     */
    public function toPhpAttribute($mongoKey);

    /**
     * Format a php attribute to a mongo key
     *
     * @param string $phpAttribute
     *
     * @return string
     */
    public function toMongoKey($phpAttribute);

    /**
     * Get a php accessor name from a php attribute
     *
     * @param string  $string    The php attribute
     * @param string  $type      The php type
     *
     * @return string
     */
    public function getPhpAccessor($string, $type = 'mixed');

    /**
     * Get a php mutator name a php attribute
     *
     * @param string  $string    The php attribute
     *
     * @return string
     */
    public function getPhpMutator($string);
}
