<?php

namespace Boomgo\Mapper;


use Boomgo\Formatter\FormatterInterface;
use Boomgo\Formatter\TransparentFormatter;

/**
 * 
 */
class SimpleMapper implements MapperInterface
{
    private $schemaLess;

    /**
     * Constructor
     * 
     * @param FormatterInterface $formatter  A formatter or null
     * @param boolean            $schemaless True to be schemaless
     */
    public function __construct($formatter = null, $schemaLess = true)
    {
        if (null === $formatter) {
            $formatter = new TransparentFormatter();
        }

        $this->setFormatter($formatter);
        $this->schemaLess = $schemaLess;
    }
    /**
     * Define the formatter
     * 
     * @param FormatterInterface $formatter [description]
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function isSchemaLess()
    {
        return $this->schemaLess;
    }

    public function toArray($object)
    {
        $reflectedObject = new \ReflectionObject($object);
        $reflectedProperties = $reflectecObject->getProperties();

        $array = array();

        foreach ($reflectedProperties as $property) {
            $value = $this->getValue($object, $property);

            if (null !== $value) {
                if (!is_scalar($value)) {
                    $value = $this->normalize($value);
                }

                $array[$this->formatter->toMongoKey($property->getName())] = $value;
            }
        }
        return $array;
    }

    public function hydrate($object, array $array)
    {
        $reflectedObject = new \ReflectionObject($object);
        $reflectedProperties = $reflectedObject->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($array as $key => $value) {
            $attributeName = $this->formatter->toPhpAttribute($key);

            if ($reflectedObject->hasProperty($attributeName)) {
                    
                $property = $reflectedObject->getProperty($attributeName);
                $this->setValue($object, $property, $value);
            } elseif ($this->isSchemaless()) {
                $object->$attributeName = $value;
            }       
        }

        return $object;
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

    /**
     * Return a value for an object property
     * 
     * @param  mixed               $object   
     * @param  \ReflectionProperty $property
     * @return mixed
     */
    private function getValue($object, \ReflectionProperty $property)
    {
        $value = null;

        if ($property->isPublic()) {
            $value = $property->getValue($object);
        } else {
            // Try to use accessor if property is not public
            $accessorName = $this->formatter->getPhpAccessor($property->getName(), false);
            $reflectedMethod = $reflectedObject->getMethod($accessorName);

            if (null !== $reflectedMethod) {
                $value = $reflectedMethod->invoke($object);
            }
        }

        return $value;
    }

    /**
     * Define a value for an object property
     * 
     * @param mixed               $object
     * @param \ReflectionProperty $property
     * @param mixed               $value
     */
    private function setValue($object, \ReflectionProperty $property, $value)
    {
        if ($property->isPublic()) {
            $property->setValue($object, $value);
        } else {
            // Try to use mutator if property is not public
            $mutatorName = $this->formatter->getPhpMutator($property->getName(), false);
            $reflectedMethod = $reflectedObject->getMethod($mutatorName);

            if (null !== $reflectedMethod) {
                $reflectedMethod->invoke($object, $value);
            }
        }
    }
}