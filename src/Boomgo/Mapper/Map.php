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

namespace Boomgo\Mapper;

/**
 * Map
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Map
{
    const NATIVE = 'NATIVE';
    const DOCUMENT = 'DOCUMENT';
    const COLLECTION = 'COLLECTION';

    /**
     * The mapped class name
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $mongoIndex;

    /**
     * @var array
     */
    private $phpIndex;

    /**
     * @var array
     */
    private $accessors;

    /**
     * @var array
     */
    private $mutators;

    /**
     * @var array
     */
    private $embedTypes;

    /**
     * @var array
     */
    private $embedMaps;

    /**
     * Constructor
     *
     * @param string $class
     * @param string $type
     */
    public function __construct($class)
    {
        $this->setClass($class);
        $this->mongoIndex = array();
        $this->phpIndex = array();
        $this->mutators = array();
        $this->accessors = array();
        $this->embedTypes = array();
        $this->embedMaps = array();
    }

    /**
     * Defines the name of the mapped class
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Returns the name of the mapped class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Adds a new entry to the map
     *
     * @param  string $key       The mongo key
     * @param  string $attribute The php attribute
     * @param  string $accessor  The php accessor
     * @param  string $mutator   The php mutator
     * @param  string $embedType The embed type (collection|document)
     * @param  string $embedMap  The embed map of the embedded document
     */
    public function add($key, $attribute, $accessor = null, $mutator = null, $embedType = null, $embedMap = null)
    {
        $this->mongoIndex[$key] = $attribute;
        $this->phpIndex[$attribute] = $key;

        if (null !== $accessor) {
            $this->addAccessor($key, $accessor);
        }

        if (null !== $mutator) {
            $this->addMutator($key, $mutator);
        }

        if (null !== $embedType) {
            $this->addEmbedType($key, $embedType);
        }

        if (null !== $embedMap) {
            $this->addEmbedMap($key, $embedMap);
        }
    }

    /**
     * Returns the mongo indexed map
     *
     * Reversed representation of the phpIndex array (no need to flip it)
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
     * Returns the php indexed map
     *
     * Reversed representation of the mongoIndex array (no need to flip it)
     * Array keys are php attribute & values are mongo key.
     *
     * @example array('phpAttribute' => 'mongoKey');
     * @return  array
     */
    public function getPhpIndex()
    {
        return $this->phpIndex;
    }

    /**
     * Checks if a php attribute exists for a mongo key
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasAttributeFor($key)
    {
        return isset($this->mongoIndex[$key]);
    }

    /**
     * Returns a php attribute corresponding to a mongo key
     *
     * @param  string $key
     * @return mixed  string|null
     */
    public function getAttributeFor($key)
    {
        return ($this->hasAttributeFor($key)) ? $this->mongoIndex[$key] : null;
    }

    /**
     * Checks if a mongo key exists for a php attribute
     *
     * @param  string  $attribute
     * @return boolean
     */
    public function hasKeyFor($attribute)
    {
        return isset($this->phpIndex[$attribute]);
    }

    /**
     * Returns a mongo key corresponding to a php attribute
     *
     * @param  string $attribute
     * @return mixed  string|null
     */
    public function getKeyFor($attribute)
    {
        return ($this->hasKeyFor($attribute)) ? $this->phpIndex[$attribute] : null;
    }

    /**
     * Returns a mongo key indexed array with all embed types
     *
     * @return array
     */
    public function getEmbedTypes()
    {
        return $this->embedTypes;
    }

    /**
     * Adds an embed type for a mongo key
     *
     * @param string $key
     * @param string $type Collection (many) or Document (one)
     */
    public function addEmbedType($key, $type)
    {
        $const = strtoupper($type);

        if ($const !== self::NATIVE && $const !== self::DOCUMENT && $const !== self::COLLECTION) {
            throw new \InvalidArgumentException('Unknown map type "'.$type.'"');
        }

        $this->embedTypes[$key] = $const;
    }

    /**
     * Checks if a mongo key has an embedded type declaration
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasEmbedTypeFor($key)
    {
       return isset($this->embedTypes[$key]);
    }

    /**
     * Returns an embedded type for a mongo key
     *
     * @param  string $key
     * @return mixed  string|null
     */
    public function getEmbedTypeFor($key)
    {
         return ($this->hasEmbedTypeFor($key)) ? $this->embedTypes[$key] : null;
    }

    /**
     * Returns a mongo indexed array with all the mapped php mutator
     *
     * @return array
     */
    public function getMutators()
    {
        return $this->mutators;
    }

    /**
     * Adds a php mutator for a mongo key
     *
     * @param string $key
     * @param string $mutator
     */
    public function addMutator($key, $mutator)
    {
        $this->mutators[$key] = $mutator;
    }

    /**
     * Checks if a mutator exists for a mongo key
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasMutatorFor($key)
    {
        return isset($this->mutators[$key]);
    }

    /**
     * Returns a mutator for a mongo key
     *
     * @param  string $key
     * @return mixed  string|null
     */
    public function getMutatorFor($key)
    {
        return ($this->hasMutatorFor($key)) ? $this->mutators[$key] : null;
    }

    /**
     * Returns an array of mapped php accessor
     *
     * @return array
     */
    public function getAccessors()
    {
        return $this->accessors;
    }

    /**
     * Adds a php accessor for a mongo key
     *
     * @param string $key
     * @param string $accessor
     */
    public function addAccessor($key, $accessor)
    {
        $this->accessors[$key] = $accessor;
    }

    /**
     * Checks if a php accessor exists for a mongo key
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasAccessorFor($key)
    {
        return isset($this->accessors[$key]);
    }

    /**
     * Returns a php accessor for a mongo key
     *
     * @param  string $key
     * @return mixed  string|null
     */
    public function getAccessorFor($key)
    {
        return ($this->hasAccessorFor($key)) ? $this->accessors[$key] : null;
    }

    /**
     * Returns a mongo key indexed array of embedded maps
     *
     * @return array
     */
    public function getEmbedMaps()
    {
        return $this->embedMaps;
    }

    /**
     * Adds an embedded map for a mongo key
     *
     * @param string $key
     * @param Map    $map
     */
    public function addEmbedMap($key, Map $map)
    {
        $this->embedMaps[$key] = $map;
    }

    /**
     * Checks if an embedded map exists for a mongo key
     *
     * @param  string  $key
     * @return boolean
     */
    public function hasEmbedMapFor($key)
    {
        return isset($this->embedMaps[$key]);
    }

    /**
     * Returns an embedded map for a mongo key
     *
     * @param  string $key
     * @return mixed  string|null
     */
    public function getEmbedMapFor($key)
    {
        return ($this->hasEmbedMapFor($key)) ? $this->embedMaps[$key] : null;
    }
}