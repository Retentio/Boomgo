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
 * Transparent Formatter
 *
 * Provide FormatterInterface implementation when mongo key are identical to php attribute
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class TransparentFormatter implements FormatterInterface
{
    /**
     * Return the exact same php attribute
     *
     * @param string $phpAttribute
     *
     * @return string $mongoKey
     */
    public function toMongoKey($phpAttribute)
    {
        return $phpAttribute;
    }

    /**
     * Return the exact same mongo key
     *
     * @param string $mongoKey
     *
     * @return string $mongoKey
     */
    public function toPhpAttribute($mongoKey)
    {
        return $mongoKey;
    }

    /**
     * Return the php attribute prefixed with get or is
     *
     * @param string  $string
     * @param string  $type
     *
     * @return string
     */
    public function getPhpAccessor($string, $type = 'mixed')
    {
        $prefix = (($type === 'bool' || $type === 'boolean') ? 'is' : 'get');

        return $prefix.$string;
    }

    /**
     * Return the php attribute always prefixed with set
     *
     * @param string  $string
     *
     * @return string
     */
    public function getPhpMutator($string)
    {
        return 'set'.$string;
    }
}