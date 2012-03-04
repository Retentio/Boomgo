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

namespace Boomgo\Console;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * Constructor.
     *
     * @param string             $name              The name of the application
     * @param string             $version           The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }
}