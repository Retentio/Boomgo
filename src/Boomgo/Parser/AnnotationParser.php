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

namespace Boomgo\Parser;

use Boomgo\Formatter\FormatterInterface;

/**
 * AnnotationParser
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class AnnotationParser implements ParserInterface
{
    /**
     * Boomgo annotation tag
     * @var string
     */
    private $annotation;

    /**
     * Constructor
     *
     * @param string $annotation
     */
    public function __construct($annotation = '@Boomgo')
    {
        $this->setAnnotation($annotation);
    }

   /**
     * Define the Boomgo annotation tag
     *
     * @param string $annotation
     */
    public function setAnnotation($annotation)
    {
        if (!preg_match('#^@[a-zA-Z0-9]+$#', $annotation)) {
             throw new \InvalidArgumentException('Boomgo annotation tag should start with "@" character');
        }

        $this->annotation = $annotation;
    }

    /**
     * Return the defined Boomgo annotation tag
     *
     * @return string
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * Extract and return Boomgo metadata from a class
     *
     * @param  string $class FQDN of the class to parse
     * @return array
     */
    public function parse($class)
    {
        $metadata = array();

        $reflectedClass = new \ReflectionClass($class);
        $reflectedProperties = $reflectedClass->getProperties();
        foreach ($reflectedProperties as $reflectedProperty) {

            if ($this->isBoomgoProperty($reflectedProperty)) {
                $propertyMetadata = $this->parseMetadata($reflectedProperty);
                $metadata[$reflectedProperty->getName()] = $propertyMetadata;
            }
        }

        return $metadata;
    }

    /**
     * Check if an object property has to be processed by Boomgo
     *
     * @param  ReflectionProperty $property the property to check
     * @throws RuntimeException If annotation is malformed
     * @return Boolean True if the property should be stored
     */
    private function isBoomgoProperty(\ReflectionProperty $property)
    {
        $propertyName = $property->getName();
        $className = $property->getDeclaringClass()->getName();

        $annotationTag = substr_count($property->getDocComment(), $this->getAnnotation());
        if (0 < $annotationTag) {
            if (1 === $annotationTag) {
                return true;
            }

            throw new \RuntimeException(sprintf('Boomgo annotation tag should occur only once for "%s->%s"', $className, $propertyName));
        }

        return false;
    }

    /**
     * Parse Boomgo metadata
     *
     * Extract metadata from the optional var tag
     *
     * @param  \ReflectionProperty $property
     * @return array
     */
    private function parseMetadata(\ReflectionProperty $property)
    {
        $metadata = array();
        $tag = '@var';
        $docComment = $property->getDocComment();
        $occurence = (int)substr_count($docComment, $tag);

        if (1 < $occurence) {
            throw new \RuntimeException(sprintf('"@var" tag is not unique', $tag));
        }

        // Grep type and optional namespaces
        preg_match('#@var\h+([a-zA-Z0-9\\\\_]+)(?:\h+\[([a-zA-Z0-9\\\\\s,_]+)\]\h*|.*)\v#', $docComment, $captured);

        if (!empty($captured)) {

            // Format var metadata
            $metadata['type'] = $captured[1];

            if (isset($captured[2])) {
                $metadata['mappedClass'] = trim($captured[2]);
            }
        }

        return $metadata;
    }
}