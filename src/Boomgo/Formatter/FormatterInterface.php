<?php

/**
 * This file is part of the Boomgo PHP ODM.
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
     * @param  string $mongoKey
     * @return string
     */
    public function toPhpAttribute($mongoKey);

    /**
     * Format a php attribute to a mongo key
     *
     * @param  string $toPhpAttribute
     * @return string
     */
    public function toMongoKey($phpAttribute);

    /**
     * Get a php accessor name from a mongo key or a php attribute
     *
     * @param  string  $string    The php attribute or the mongo key
     * @param  boolean $fromMongo True if you provided a mongo key string, false for a php attribute
     * @return string
     */
    public function getPhpAccessor($string, $fromMongo = true);

    /**
     * Get a php mutator name from a mongo key or a php attribute
     *
     * @param  string  $string    The php attribute or the mongo key
     * @param  boolean $fromMongo True if you provided a mongo key string, false for a php attribute
     * @return string
     */
    public function getPhpMutator($string, $fromMongo = true);
}
