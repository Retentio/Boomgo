<?php 

namespace Boomgo\Mapper;

class Map
{
    const DOCUMENT = 'DOCUMENT';
    const COLLECTION = 'COLLECTION';

    private $class;

    private $index;

    private $accessors;

    private $mutators;

    private $embedTypes;

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

        $this->index = array();
        $this->mutators = array();
        $this->accessors = array();
        $this->embedTypes = array();
        $this->embedMaps = array();
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function add($key, $attribute, $accessor = null, $mutator = null, $embedType = null, $embedMap = null)
    {
        $this->index[$key] = $attribute;

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


    public function getIndex()
    {
        return $this->index;
    }

    public function getEmbedTypes()
    {
        return $this->embedTypes;
    }

    public function addEmbedType($key, $type)
    {
        $const = strtoupper($type);

        if ($const !== self::DOCUMENT && $const !== self::COLLECTION) {
            throw new \InvalidArgumentException('Unknown map type "'.$type.'"');
        }

        $this->embedTypes[$key] = $const;
    }

    public function hasEmbedTypeFor($key)
    {
       return isset($this->embedTypes[$key]);
    }

    public function getEmbedTypeFor($key)
    {
         return ($this->hasEmbedTypeFor($key)) ? $this->embedTypes[$key] : null;
    }

    public function getMutators()
    {
        return $this->mutators;
    }

    public function addMutator($key, $mutator)
    {
        $this->mutators[$key] = $mutator;
    }

    public function hasMutatorFor($key)
    {
        return isset($this->mutators[$key]);
    }

    public function getMutatorFor($key)
    {
        return $this->hasMutatorFor($key) ? $this->mutators[$key] : null;
    }

    public function getAccessors()
    {
        return $this->accessors;
    }

    public function addAccessor($key, $accessor)
    {
        $this->accessors[$key] = $accessor;
    }
  
    public function hasAccessorFor($key)
    {
        return isset($this->accessors[$key]);
    }
    public function getAccessorFor($key)
    {
        return $this->hasAccessorFor($key) ? $this->accessors[$key] : null;
    }

    public function getEmbedMaps()
    {
        return $this->embedMaps;
    }

    public function addEmbedMap($key, Map $map)
    {
        $this->embedMaps[$key] = $map;
    }

    public function hasEmbedMapFor($key)
    {
        return isset($this->embedMaps[$key]);
    }

    public function getEmbedMapFor($key)
    {
        return $this->hasEmbedMapFor($key) ? $this->embedMaps[$key] : null;
    }

    public function getKeys()
    {
        return array_keys($this->index);
    }

    public function getAttributes()
    {
        return array_values($this->index);
    }

    public function getAttributeFor($key)
    {
        return $this->index[$key];
    }
}