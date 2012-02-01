<?php 

namespace Boomgo\Mapper;

abstract class MapperProvider implements MapperInterface
{
    abstract public function getMap($class);

    /**
     * Convert this object to array
     * 
     * @param  object  $object  An object to convert.
     * @return Array
     */
    public function toArray($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Argument must be an object');
        }

        $reflectedObject = new \ReflectionObject($object);
        $className = $reflectedObject->getName();

        $map = $this->getMap($className);

        $array = array();

        $attributes = $map->getIndex(); 
        

        foreach ($attributes as $key => $attribute) {

            if ($map->hasAccessorFor($key)) {
                $accessor = $map->getAccessorFor($key);
                $value = $object->$accessor();
            } else {
                $value = $object->$attribute;
            }

            // Recursively normalize nested non-scalar data
            if (null !== $value && !is_scalar($value)) {
                $value = $this->normalize($value);
            }

            $array[$key] = $value;
        }

        // If all keys has a null value, we should return an empy array.
        // PHP suck balls (isset, empty, array_value)
        if (!array_filter($array)) {
            $array = array();
        }

        return $array;
    }

    /**
     * Hydrate an object
     * 
     * @param  string $object A full qualified domain name or an object
     * @param  array  $array An array of data from mongo
     * @return object
     */
    public function hydrate($object, array $array)
    {
        if (is_string($object)) {
            $className = $object;

            $reflectedClass = new \ReflectionClass($className);
            $constructor = $reflectedClass->getConstructor();

            if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
                throw new \RuntimeException('Unable to hydrate an object requiring constructor param');
            }

            $object = new $object;

        } elseif (is_object($class)) {
            $reflectedObject = new \ReflectionObject($object);
            $className = $reflectedObject->getName();
        }

        $map = $this->getMap($className);

        foreach ($array as $key => $value) {
            if (null !== $value) {

                $attribute = $map->getAttributeFor($key);

                if ($map->hasEmbedMapFor($key)) {
                    $value = $this->hydrateEmbed($map, $key, $value);
                }

                if ($map->hasMutatorFor($key)) {
                    $mutator = $map->getMutatorFor($key);
                    $object->$mutator($value);
                } else {
                    $object->$attribute = $value;
                }
            }
        }
        return $object;
    }

    /**
     * Hydrate embed documents from a super Map
     * 
     * @param  Map    $map    The super map
     * @param  string $key    The key defined as embedding doc
     * @param  mixed  $value  The embed data
     * @return mixed
     */
    protected function hydrateEmbed(Map $map, $key, $value)
    {
        // Embed declaration
        $embedType = $map->getEmbedTypeFor($key);
        $embedMap = $map->getEmbedMapFor($key);

        if (!is_array($value)) {
            throw new \RuntimeException('Embedded document or collection expect an array');
        }

        if ($embedType == Map::DOCUMENT) {
            // Embed document
            
            // Expect an hash (associative array), @todo maybe remove this check ?
            if (array_keys($value) === range(0, sizeof($value) - 1)) {
                throw new \RuntimeException('Embedded document expect an associative array');
            }

            $value = $this->hydrate($embedMap->getClass(), $value);

        } elseif ($embedType == Map::COLLECTION) {
            // Embed collection
             
            // Expect an array (numeric array), @todo maybe remove this check ?
            if (array_keys($value) !== range(0, sizeof($value) - 1)) {
                throw new \RuntimeException('Embedded collection expect a numeric-indexed array');
            }

            $collection = array();

            // Recursively hydrate embed documents
            foreach ($value as $embedValue) {
               $collection[] = $this->hydrate($embedMap->getClass(), $embedValue);
            }

            $value = $collection;
        }

        return $value;
    }

    /**
     * Normalize php data for mongo
     * 
     * This code chunk was inspired by the Symfony framework
     * and is subject to the MIT license. Please see the LICENCE
     * at https://github.com/symfony/symfony
     * 
     * (c) Fabien Potencier <fabien@symfony.com>
     * @author Nils Adermann <naderman@naderman.de>
     * 
     * @param  mixed $data
     * @return mixed
     */
    protected function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }
        if (is_object($data)) {
            return $this->toArray($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->normalize($val);
            }

            return $data;
        }
        throw new \RuntimeException('An unexpected value could not be normalized: '.var_export($data, true));
    }
}