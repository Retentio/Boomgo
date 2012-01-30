<?php

namespace Boomgo;

use Boomgo\Mapper\Map;
use Boomgo\Parser\ParserInterface;
use Boomgo\Formatter\FormatterInterface;

/**
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Mapper
{
    private $parser;

    /**
     * Constructor
     * 
     * @param FormatterInterface An key/attribute formatter
     * @param string $annotation The annotation used for mapping
     */
    public function __construct(ParserInterface $parser)
    {
        $this->setParser($parser);
    }

    /**
     * Define the parser to use
     * 
     * @param ParserInterface $parser
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Return the parser used
     * 
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Normalize data for mongo
     * 
     * This code chunk was extract from the Symfony framework
     * and is subject to the MIT license. Please see the LICENCE
     * at https://github.com/symfony/symfony
     * 
     * (c) Fabien Potencier <fabien@symfony.com>
     * @author Nils Adermann <naderman@naderman.de>
     * 
     * @param  mixed $data
     * @return mixed
     */
    public function normalize($data)
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

        $map = $this->parser->getMap($reflectedObject->getName());

        $array = array();

        // Assert that a stand alone document must have an id field
        // $hasIdKey = $this->parser->hasValidIdentifier($reflectedObject);
        
        // // Fetch mandatory _id first
        // if ($hasIdKey) {
        //     $array['_id'] = $object->getId();
        // }
        
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

        // Unset potential id field since we firstly processed _id
        // if ($hasIdKey) {
        //     unset($array['id']);
        // }

        // If all keys has a null value, we should return an empy array.
        // PHP suck balls (isset, empty, array_value)
        if (!array_filter($array)) {
            $array = array();
        }

        return $array;
    }

    /**
     * Denormalize mongo data for php
     * 
     * @param  mixed $data
     * @return mixed
     */
    public function denormalize($data)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->denormalize($val);
            }
            return $data;
        }
        throw new \RuntimeException('An unexpected value could not be normalized: '.var_export($data, true));
    }

    /**
     * Hydrate an object
     * 
     * @param  array  $array     An array of data from mongo
     * @param  string $className A full qualified domain name
     * @return object
     */
    public function hydrate($className, array $array)
    {
        // $reflectedClass = new \ReflectionClass($className);
        // $constructor = $reflectedClass->getConstructor();

        // if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
        //     throw new \RuntimeException('Unable to hydrate object requiring constructor param');
        // }

        $object = new $className;

        // if (isset($array['_id'])) {
        //     if (!$this->parser->hasValidIdentifier($reflectedObject)) {
        //         throw new \RuntimeException('Object do not handle identifier');
        //     }
        //     // Php Document identifier convention is "id" not "_id" 
        //     $array['id'] = $array['_id'];
        //     unset($array['_id']);
        // }

        $map = $this->parser->getMap($className);

        foreach ($array as $key => $value) {
            if (null !== $value) {

                $attribute = $map->getAttributeFor($key);

                if ($map->hasEmbedMapFor($key)) {
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
                } else {
                    if (!is_scalar($value)) {
                        // No embed declaration (document/collection), process as a regular array
                        $value = $this->denormalize($value);
                    }
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
}