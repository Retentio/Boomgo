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

namespace Boomgo\Parser;

use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;

/**
 * ParserInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface ParserInterface
{
    public function setFormatter(FormatterInterface $formatter);

    public function getFormatter();

    public function buildMap($class, $dependenciesGraph = null);
}