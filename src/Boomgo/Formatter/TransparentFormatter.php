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
 * Transparent Formatter
 *
 * Provide FormatterInterface implementation when mongo key are identical to php attribute
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class TransparentFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function toMongoKey($phpAttribute)
    {
        return $phpAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function toPhpAttribute($mongoKey)
    {
        return $mongoKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpAccessor($string, $type = 'mixed', $fromMongo = true)
    {
        return 'get'.$string;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpMutator($string, $type ='mixed', $fromMongo = true)
    {
        return 'set'.$string;
    }
}