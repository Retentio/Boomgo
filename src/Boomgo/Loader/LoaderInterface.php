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

namespace Boomgo\Loader;

/**
 * MapperInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface LoaderInterface
{
    public function load($resource);
}