<?php

namespace Boomgo;

use Boomgo\Formatter\FormatterInterface;

/**
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Mapper
{
    private $annotation;

    private $formatter;

    /**
     * Constructor
     * 
     * @param string $annotation
     */
    public function __construct(FormatterInterface $formatter, $annotation = '@Boomgo')
    {
        $this->setFormatter($formatter);
        $this->setAnnotation($annotation);
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
     * Define the annotation for the mapper instance
     * 
     * @param string $annotation
     */
    public function setAnnotation($annotation)
    {
        if (!preg_match('#^@[a-zA-Z]+$#', $annotation)) {
             throw new \InvalidArgumentException('Annotation should start with @ char');
        }
        
        $this->annotation = $annotation;
    }

    /**
     * Return the annotation defined for the mapper instance
     * 
     * @return string
     */
    public function getAnnotation()
    {
        return $this->annotation;
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
        $hasIdKey = $this->hasValidIdentifier($reflectedObject);
        
        // Fetch mandatory _id first
        if ($hasIdKey) {
            $array['_id'] = $object->getId();
        }

        $reflectedProperties = $reflectedObject->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {
            if ($this->isBoomgoProperty($reflectedProperty)) {
                $accessorName = 'get'.ucfirst($reflectedProperty->getName());
                
                if ($reflectedObject->hasMethod($accessorName)) {
                    $reflectedMethod = $reflectedObject->getMethod($accessorName);

                    if ($this->isValidAccessor($reflectedMethod)) {
                        $key = $this->formatter->toMongoKey($reflectedProperty->getName());
                        $value = $reflectedMethod->invoke($object);

                        // Recursively normalize nested non-scalar data
                        if (null !== $value && !is_scalar($value)) {
                            $value = $this->normalize($value);
                        }

                        $array[$key] = $value;
                    }
                }
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
            if (!$this->hasValidIdentifier($reflectedObject)) {
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

                    if ($this->isBoomgoProperty($reflectedProperty)) {
                        $reflectedMethod = $reflectedObject->getMethod($mutatorName);

                        if ($this->isValidMutator($reflectedMethod)) {

                            // Recursively normalize nested non-scalar data
                            if (!is_scalar($value)) {

                                $metadata = array();

                                if (is_array($value)) {
                                    $metadata = $this->parseMetadata($reflectedProperty);
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

    /**
     * Check if an object property should be persisted.
     *
     * @param  ReflectionProperty $property the property to check
     * @throws RuntimeException If annotation is malformed
     * @return Boolean True if the property should be stored
     */
    public function isBoomgoProperty(\ReflectionProperty $property)
    {
        $boomgoAnnot = substr_count($property->getDocComment(), $this->getAnnotation());

        if (0 < $boomgoAnnot) {
            if (1 === $boomgoAnnot) {
                return true;
            }
            throw new \RuntimeException('Boomgo annotation should occur only once');
        }

        return false;
    }

    /**
     * Parse Boomgo metadata
     * 
     * @param  \ReflectionProperty $property
     * @return array
     */
    public function parseMetadata(\ReflectionProperty $property)
    {
        $metadata = array();

        preg_match('#'.$this->getAnnotation().'\s*([a-zA-Z]*)\s*([a-zA-Z\\\\]*)\s*\v*#', $property->getDocComment(), $metadata);

        if (empty($metadata) || sizeof($metadata) > 3 || 
            (!empty($metadata[1]) && empty($metadata[2]))) {
            throw new \RuntimeException('Malformed metadata');
        }

        array_shift($metadata);

        return $metadata[1] ? $metadata : array();
    }

    /**
     * Check if a php document handle an identifier
     *  
     * @param  ReflectionObject  $object
     * @return boolean
     */
    public function hasValidIdentifier(\ReflectionObject $object)
    {
        if ($object->hasProperty('id') && $object->hasMethod('getId') && $object->hasMethod('setId')) {

            if ($this->isBoomgoProperty($object->getProperty('id')))
            {
                if(!($this->isValidAccessor($object->getMethod('getId'))) ||
                   !($this->isValidMutator($object->getMethod('setId')))) {

                    throw new \RuntimeException('Object expect an id but do not expose valid accessor/mutator');
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the getter is public and has no required argument.
     * 
     * @param  ReflectionMethod $method the method to check
     * @return Boolean True if the getter is valid
     */
    public function isValidAccessor(\ReflectionMethod $method)
    {
        return ($method->isPublic() && 
                0 === $method->getNumberOfRequiredParameters());
    }

    /**
     * Check if the setter is public and has one required argument.
     * 
     * @param  ReflectionMethod $method the method to check
     * @return Boolean True if the setter is valid
     */
    public function isValidMutator(\ReflectionMethod $method)
    {
        return ($method->isPublic() && 
                1 === $method->getNumberOfRequiredParameters());
    }
}