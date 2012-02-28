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

namespace Boomgo;

/**
 * Map
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Map
{
    /**
     * @var string The mapped FQDN
     */
    public $class;

    /**
     * @var array Indexed by "MongoDB keys" where are "PHP attributes"
     */
    public $mongoIndex;

    /**
     * @var array Indexed by "PHP attributes"
     */
    public $definitions;

    /**
     * @var array Indexed by "PHP class"
     */
    public $dependencies;

    /**
     * Return class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return all the definitions
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Check if a definition exists
     *
     * @param  string $identifier
     * @return boolean
     */
    public function hasDefinition($identifier)
    {
        return isset($this->definitions[$identifier]) || isset($this->mongoIndex[$identifier]);
    }

    /**
     * Return a definition
     *
     * @param  string $identifier
     * @return mixed  null|Definition
     */
    public function getDefinition($identifier)
    {
        // Identifier is a php attribute
        if (isset($this->definitions[$identifier])) {
            return $this->definitions[$identifier];
        }

        // Identifier is a MongoDB Key
        if (isset($this->mongoIndex[$identifier])) {
            return $this->definitions[$this->mongoIndex[$identifier]];
        }

        return null;
    }

    /**
     * Checks if an embedded map exists for a class
     *
     * @param  string  $class
     * @return boolean
     */
    public function hasDependency($class)
    {
        return isset($this->dependencies[$class]);
    }

    /**
     * Returns an embedded map for a mongo key
     *
     * @param  string $class
     * @return mixed  null|Map
     */
    public function getDependency($class)
    {
        return ($this->hasDependency($class)) ? $this->dependencies[$class] : null;
    }
}