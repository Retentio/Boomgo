<?php 

namespace Boomgo\Mapper;

class Map
{
    const DOCUMENT = 'DOCUMENT';
    const COLLECTION = 'COLLECTION';

    private $type;

    private $class;

    private $index;

    private $mutators;

    private $embedMaps;

    /**
     * Constructor
     * 
     * @param string $class
     * @param string $type
     */
    public function __construct($class, $type)
    {
        $this->setClass($class);
        $this->setType($type);

        $this->index = array();
        $this->mutators = array();
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

    public function setType($type)
    {
        $const = strtoupper($type);

        if ($const !== self::DOCUMENT && $const !== self::COLLECTION) {
            throw new \InvalidArgumentException('Unknown map type "'.$type.'"');
        }

        $this->type = $const;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getMutators()
    {
        return $this->mutators;
    }

    public function getEmbedMaps()
    {
        return $this->embedMaps;
    }

    public function add($key, $attribute, $mutator = null, $map = null)
    {
        $this->index[$key] = $attribute;

        if (null !== $mutator) {
            $this->addMutator($key, $mutator);
        }

        if (null !== $map) {
            $this->addEmbedMap($key, $map);
        }
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

    public function hasEmbedMapFor($key)
    {
        return isset($this->embedMaps[$key]);
    }

    public function getEmbedMapFor($key)
    {
        return $this->hasEmbedMapFor($key) ? $this->embedMaps[$key] : null;
    }

    public function addEmbedMap($key, Map $map)
    {
        $this->embedMaps[$key] = $map;
    }

    public function hasMutatorFor($key)
    {
        return isset($this->mutators[$key]);
    }

    public function addMutator($key, $mutator)
    {
        if (!is_string($key) ||
            !is_string($mutator) ||
            !preg_match('#^[a-zA-Z0-9_-]+$#', $mutator)) {
            throw new \InvalidArgumentException('Invalid key or mutator');
            }
        $this->mutators[$key] = $mutator;
    }

    public function getMutatorFor($key)
    {
        return $this->hasMutatorFor($key) ? $this->mutators[$key] : null;
    }
}