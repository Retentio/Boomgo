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

namespace Boomgo\Builder;

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
    private $class;

    /**
     * @var array Php attributes name indexed by MongoDB keys name
     */
    private $mongoIndex;

    /**
     * @var array Definitions indexed by "PHP attributes"
     */
    private $definitions;

    /**
     * Constructor
     *
     * @param string $class The mapped FQDN
     */
    public function __construct($class)
    {
        $this->class = (strpos($class, '\\') === 0) ? $class : '\\'.$class;
        $this->mongoIndex = array();
        $this->definitions = array();
        $this->dependencies = array();
    }

    /**
     * Return the FQDN of the mapped class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return the mapped short class name
     *
     * @return string
     */
    public function getClassName()
    {
        $array = explode('\\', $this->class);

        return $array[count($array)-1];
    }

    /**
     * Return the mapped namespace without the short class name
     *
     * @return string
     */
    public function getNamespace()
    {
        $array = explode('\\', $this->class);
        unset($array[count($array)-1]);

        return implode('\\', $array);
    }

    /**
     * Return the mongo indexed map
     *
     * Array keys are mongo keys & array values are php attributes
     *
     * @example array('mongoKey' => 'phpAttribute');
     *
     * @return  array
     */
    public function getMongoIndex()
    {
        return $this->mongoIndex;
    }

    /**
     * Return an array of definitions indexed by php attribute name
     *
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
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
     * Check whether a definition exists
     *
     * @param string $identifier Php attribute name or Document key name
     *
     * @return boolean
     */
    public function hasDefinition($identifier)
    {
        return isset($this->definitions[$identifier]) || isset($this->mongoIndex[$identifier]);
    }

    /**
     * Return a definition
     *
     * @param string $identifier Php attribute name or Document key name
     *
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