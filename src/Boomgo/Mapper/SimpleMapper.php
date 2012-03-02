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

use Boomgo\Formatter\FormatterInterface;

/**
 * SimpleMapper
 *
 * Live mapper allowing MongoDB schemaless feature
 * Rely on dynamic object analyze (do not use a Map definition)
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class SimpleMapper extends MapperProvider implements MapperInterface
{
    /**
     * @var boolean
     */
    private $schemaLess;

    /**
     * Constructor
     *
     * @param FormatterInterface $formatter  A formatter
     * @param boolean            $schemaless True to enable schemaless
     */
    public function __construct(FormatterInterface $formatter, $schemaLess = true)
    {
        $this->setFormatter($formatter);
        $this->schemaLess = $schemaLess;
    }

    /**
     * Define the formatter
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Return the formatter used
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Enable or disable schema less
     *
     * @param boolean $switch
     */
    public function setSchemaLess($switch)
    {
        $this->schemaLess = $switch;
    }

    /**
     * Return whether schema less is enabled
     *
     * @return boolean
     */
    public function isSchemaLess()
    {
        return $this->schemaLess;
    }

    /**
     * Convert an object to a mongoable array
     *
     * @param  mixed $object
     * @return array
     */
    public function serialize($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Argument must be an object');
        }

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

    /**
     * Hydrate an object
     *
     * If schemaless is enabled, any data in the array
     * will be dynamically appended to the object
     *
     * @param  mixed  $object
     * @param  array  $array
     * @return mixed
     */
    public function hydrate($object, array $array)
    {
        $reflected = new \ReflectionClass($object);

        if (is_string($object)) {
            $object = $this->createInstance($reflected);
        }

        $reflectedProperties = $reflected->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($array as $key => $value) {
            $attributeName = $this->formatter->toPhpAttribute($key);

            if ($reflected->hasProperty($attributeName)) {

                $property = $reflected->getProperty($attributeName);
                $this->setValue($object, $property, $value);
            } elseif ($this->isSchemaless()) {
                $object->$attributeName = $value;
            }
        }

        return $object;
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