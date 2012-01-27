<?php

namespace Boomgo;

use Boomgo\Parser\ParserInterface;
use Boomgo\Formatter\FormatterInterface;

/**
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Mapper
{
    private $parser;

    private $formatter;

    private $annotation;
    /**
     * Constructor
     * 
     * @param FormatterInterface An key/attribute formatter
     * @param string $annotation The annotation used for mapping
     */
    public function __construct(ParserInterface $parser, FormatterInterface $formatter)
    {
        $this->setParser($parser);
        $this->setFormatter($formatter);
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
     * Define the key/attribute formatter
     * 
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;    
    }

    /**
     * Return the key/attribute formatter
     * 
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
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
     * @param  Boolean $embedId True to force _id in embedded document
     * @return Array
     */
    public function toArray($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Argument must be an object');
        }

        $reflectedObject = new \ReflectionObject($object);
        $array = array();

        // Assert that a stand alone document must have an id field
        $hasIdKey = $this->parser->hasValidIdentifier($reflectedObject);
        
        // Fetch mandatory _id first
        if ($hasIdKey) {
            $array['_id'] = $object->getId();
        }

        $reflectedProperties = $reflectedObject->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {
            if ($this->parser->isBoomgoProperty($reflectedProperty)) {

                $attributeName = $reflectedProperty->getName();
                $keyName = $this->formatter->toMongoKey($attributeName);

                if (!$reflectedProperty->isPublic()) {
                    $accessorName = 'get'.ucfirst($attributeName);
                    $mutatorName = 'set'.ucfirst($attributeName);

                    if (!$reflectedObject->hasMethod($accessorName) ||
                        !$reflectedObject->hasMethod($mutatorName)) {
                        throw new \RuntimeException('Missing accessor/mutator for a private Boomgo property :'.$attributeName);
                    }
                        
                    $reflectedAccessor = $reflectedObject->getMethod($accessorName);
                    $reflectedMutator = $reflectedObject->getMethod($mutatorName);

                    if (!$this->parser->isValidAccessor($reflectedAccessor) ||
                        !$this->parser->isValidMutator($reflectedMutator)) {
                        throw new \RuntimeException('Invalid accessor/mutator for a private Boomgo property :'.$attributeName);
                    }
                }

                $value = $reflectedAccessor->invoke($object);

                // Recursively normalize nested non-scalar data
                if (null !== $value && !is_scalar($value)) {
                    $value = $this->normalize($value);
                }

                $array[$keyName] = $value;
            }
        }

        // Unset potential id field since we firstly processed _id
        if ($hasIdKey) {
            unset($array['id']);
        }

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
    public function denormalize($data , $className = null)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }
        if (is_array($data)) {

            if (array_keys($data) !== range(0, sizeof($data) - 1) && $className) {
                $data = $this->hydrate($className, $data);
            }

            foreach ($data as $key => $val) {
                $data[$key] = $this->denormalize($val, $className);
            }
            return $data;
        }
        echo 'called with classname : '.$className;
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
        $reflectedClass = new \ReflectionClass($className);
        $constructor = $reflectedClass->getConstructor();

        if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new \RuntimeException('Unable to hydrate object requiring constructor param');
        }

        $object = new $className;
        $reflectedObject = new \ReflectionObject($object);

        if (isset($array['_id'])) {
            if (!$this->parser->hasValidIdentifier($reflectedObject)) {
                throw new \RuntimeException('Object do not handle identifier');
            }
            // Php Document identifier convention is "id" not "_id" 
            $array['id'] = $array['_id'];
            unset($array['_id']);
        }

        foreach ($array as $key => $value) {
            if (null !== $value) {
                $attributeName = $this->formatter->toPhpAttribute($key);
                $mutatorName = 'set' . ucfirst($attributeName);

                if ($reflectedObject->hasProperty($attributeName) && $reflectedObject->hasMethod($mutatorName)) {
                    $reflectedProperty = $reflectedObject->getProperty($attributeName);

                    if ($this->parser->isBoomgoProperty($reflectedProperty)) {
                        $reflectedMethod = $reflectedObject->getMethod($mutatorName);

                        if ($this->parser->isValidMutator($reflectedMethod)) {

                            // Recursively normalize nested non-scalar data
                            if (!is_scalar($value)) {

                                $metadata = array();

                                if (is_array($value)) {
                                    $metadata = $this->parser->parseMetadata($reflectedProperty);
                                }

                                $value = $this->denormalize($value, !empty($metadata) ? $metadata[1] : null);
                            }

                            $reflectedMethod->invoke($object, $value);
                        } else {
                            if (!$reflectedProperty->isPublic()) {
                                throw new \RuntimeException('Unable to hydrate a Boomgo private property without valid mutator');
                            }
                            $reflectedProperty->setValue($object, $value);
                        }
                    } else {
                        //throw new \RuntimeException('Key conflict with an non-boomgo attribute');
                    }
                }
            }
        }

        return $object;
    }
}