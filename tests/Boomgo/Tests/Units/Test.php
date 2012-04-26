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

namespace Boomgo\Tests\Units;

use mageekguy\atoum\test as AtoumTest,
    mageekguy\atoum\factory;

/**
 * Rewrite default namespace for atoum
 * 
 * @author David Guyon <dguyon@gmail.com>
 */
abstract class Test extends AtoumTest
{
    public function __construct(factory $factory = null)
    {
        $this->setTestNamespace('\\Tests\\Units\\');
        parent::__construct($factory);
    }
}