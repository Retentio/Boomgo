<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
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
     * Boomgo global annotation tag
     * @var string
     */
    private $globalAnnotation;

    private $localAnnotation;

    /**
     * Constructor
     *
     * @param string $annotation
     */
    public function __construct($globalTag = '@Boomgo', $localTag = '@Persistent')
    {
        $this->setGlobalAnnotation($globalTag);
        $this->setLocalAnnotation($localTag);
    }

   /**
     * Define the global annotation tag
     *
     * @param string $tag
     */
    public function setGlobalAnnotation($tag)
    {
        if (!preg_match('#^@[a-zA-Z0-9]+$#', $tag)) {
             throw new \InvalidArgumentException('Boomgo annotation tag should start with "@" character');
        }

        $this->globalAnnotation = $tag;
    }

    /**
     * Return the defined global annotation tag
     *
     * @return string
     */
    public function getGlobalAnnotation()
    {
        return $this->globalAnnotation;
    }

    /**
     * Define the local annotation tag
     *
     * @param string $tag
     */
    public function setLocalAnnotation($tag)
    {
        if (!preg_match('#^@[a-zA-Z0-9]+$#', $tag)) {
             throw new \InvalidArgumentException('Boomgo annotation tag should start with "@" character');
        }

        $this->localAnnotation = $tag;
    }

    /**
     * Return the local annotation tag
     *
     * @return string
     */
    public function getLocalAnnotation()
    {
        return $this->localAnnotation;
    }

    /**
     * ParserInterface implementation
     *
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return 'php';
    }

    /**
     * ParserInterface implementation
     *
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * ParserInterface implementation
     *
     * {@inheritdoc}
     */
    public function parse($filepath)
    {
        // Regexp instead of tokens because of the bad perf @link > https://gist.github.com/1886076
        if (!preg_match('#^namespace\s+(.+?);.*class\s+(\w+).+;$#sm', file_get_contents($filepath), $captured)) {
            throw new \RuntimeException('Unable to find namespace or class declaration');
        }

        $fqcn = $captured[1].'\\'.$captured[2];
        $metadata = array();

        $reflectedClass = new \ReflectionClass($fqcn);
        $metadata['class'] = $reflectedClass->getName();

        $reflectedProperties = $reflectedClass->getProperties();
        foreach ($reflectedProperties as $reflectedProperty) {

            if ($this->isBoomgoProperty($reflectedProperty)) {
                $propertyMetadata = $this->parseMetadata($reflectedProperty);
                $metadata['definitions'][$reflectedProperty->getName()] = $propertyMetadata;
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

        $annotationTag = substr_count($property->getDocComment(), $this->getLocalAnnotation());
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
            throw new \RuntimeException(sprintf('"@var" tag is not unique for "%s->%s"', $property->getDeclaringClass()->getName(), $property->getName()));
        }

        $metadata['attribute'] = $property->getName();

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