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

namespace Boomgo\Mapper\Map;

use Boomgo\Mapper\Map;

/**
 * Native map for MongoId
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class MongoId extends Map
{
    /**
     * Constructor
     *
     * @param string $class
     * @param string $type
     */
    public function __construct()
    {
        $this->setClass('\\MongoId');
        $this->mongoIndex = array();
        $this->phpIndex = array();
        $this->mutators = array();
        $this->accessors = array();
        $this->embedTypes = array();
        $this->embedMaps = array();
        $this->add('id', 'id');
    }
}