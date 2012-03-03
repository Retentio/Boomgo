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

namespace Boomgo\Builder;

/**
 * Map pan
 *
 * Map master used to build the lighter exported map
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Map
{
    /**
     * @var string The mapped FQDN
     */
    private $class;

    /**
     * @var array Indexed by "MongoDB keys" where are "PHP attributes"
     */
    private $mongoIndex;

    /**
     * @var array Indexed by "PHP attributes"
     */
    private $definitions;

    /**
     * @var array Indexed by "PHP attributes"
     */
    private $dependencies;

    /**
     * Constructor
     *
     * @param string $class The mapped FQDN
     */
    public function __construct($class)
    {
        $this->class = (strpos($class,'\\') === 0) ? $class : '\\'.$class;
        $this->mongoIndex = array();
        $this->definitions = array();
        $this->dependencies = array();
    }

    /**
     * Returns the FQDN of the mapped class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    public function getClassName()
    {
        $array = explode('\\', $this->class);
        return $array[count($array)-1];
    }

    public function getNamespace()
    {
        $array = explode('\\', $this->class);
        unset($array[count($array)-1]);
        return implode('\\', $array);
    }

    /**
     * Returns the mongo indexed map
     *
     * Reversed index array (no need to flip it)
     * Array keys are mongo keys & values are php attribute.
     *
     * @example array('mongoKey' => 'phpAttribute');
     * @return  array
     */
    public function getMongoIndex()
    {
        return $this->mongoIndex;
    }

    /**
     * Returns a mongo key indexed array of embedded maps
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Returns a mongo key indexed array of embedded maps
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Add a definition
     *
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $attribute = $definition->getAttribute();
        $key = $definition->getKey();

        $this->mongoIndex[$key] = $attribute;

        $this->definitions[$attribute] = $definition;
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
}