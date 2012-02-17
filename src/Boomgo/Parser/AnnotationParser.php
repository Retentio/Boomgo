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

use Boomgo\Mapper\Map;
use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;

/**
 * AnnotationParser
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class AnnotationParser extends ParserProvider implements ParserInterface
{
    /**
     * Tag used to mark persistent attributes
     * @var string
     */
    private $annotation;

    /**
     * Initialize
     *
     * @param FormmatterInterface $formatter
     * @param string $annotation
     */
    public function __construct(FormatterInterface $formatter, $annotation = '@Boomgo')
    {
        parent::__construct($formatter);
        $this->setAnnotation($annotation);
    }

   /**
     * Define the annotation for the mapper instance
     *
     * @param string $annotation
     */
    public function setAnnotation($annotation)
    {
        if (!preg_match('#^@[a-zA-Z]+$#', $annotation)) {
             throw new \InvalidArgumentException('Boomgo annotation tag should start with "@" character');
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
     * Return the data map
     *
     * @param  string $class
     * @param  array  $dependenciesGraph
     * @return array
     */
    public function buildMap($class, $dependenciesGraph = null)
    {
        $dependenciesGraph = $this->updateDependencies($class, $dependenciesGraph);

        $reflectedClass = new \ReflectionClass($class);

        $map = new Map($class);

        $reflectedProperties = $reflectedClass->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {

            if ($this->isBoomgoProperty($reflectedProperty)) {

                $attributeName = $reflectedProperty->getName();
                $keyName = $this->formatter->toMongoKey($attributeName);
                $accessorName = null;
                $mutatorName = null;
                $embedType = null;
                $embedMap = null;

                if (!$reflectedProperty->isPublic()) {
                    $accessorName = $this->formatter->getPhpAccessor($attributeName, false);
                    $mutatorName = $this->formatter->getPhpMutator($attributeName, false);

                    if (!$reflectedClass->hasMethod($accessorName) ||
                        !$reflectedClass->hasMethod($mutatorName)) {
                        throw new \RuntimeException('Missing accessor/mutator for a private Boomgo property :'.$attributeName);
                    }

                    $reflectedAccessor = $reflectedClass->getMethod($accessorName);
                    $reflectedMutator = $reflectedClass->getMethod($mutatorName);

                    if (!$this->isValidAccessor($reflectedAccessor) ||
                        !$this->isValidMutator($reflectedMutator)) {
                        throw new \RuntimeException('Invalid accessor/mutator for a private Boomgo property :'.$attributeName);
                    }
                }

                $metadata = $this->parseMetadata($reflectedProperty);

                if (!empty($metadata)) {
                    $type = $metadata[0];
                    $summary = (isset($metadata[1])) ? $metadata[1] : null;

                    if ($this->isCompositeType($type)) {
                        $embedDefinition = $this->getEmbedDefinition($type, $summary);
                        if (null !== $embedDefinition) {

                            if (is_array($embedDefinition)) {
                                $embedType = Map::COLLECTION;
                                $embedClass = $embedDefinition[1];
                            } else {
                                $embedType = Map::DOCUMENT;
                                $embedClass = $embedDefinition;
                            }

                            if ($this->isNativeSupported($embedClass)) {
                                $embedType = null;
                                $embedClass = null;
                            } else {
                                $embedMap = $this->buildMap($embedClass, $dependenciesGraph);
                            }
                        }
                    }
                }

                $map->add($keyName, $attributeName, $accessorName, $mutatorName, $embedType, $embedMap);
            }
        }
        return $map;
    }

    /**
     * Check if an object property should be persisted.
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
     * Order of match try to improve performance
     *
     * @param  \ReflectionProperty $property
     * @return array
     */
    public function parseMetadata(\ReflectionProperty $property)
    {
        $propertyName = $property->getName();
        $className = $property->getDeclaringClass()->getName();
        $metadata = array();

        $varTag = substr_count($property->getDocComment(), '@var');

        if (0 === $varTag) {
            return array();
            // @TODO For the moment Boomgo do not force or recommend @var
            // trigger_error(sprintf('Boomgo annoted property in "%s->%s" should use @var tag', $className, $propertyName), E_USER_WARNING);
        } elseif (1 < $varTag) {
            throw new \RuntimeException(sprintf('@var tag should occur only once for "%s->%s"', $className, $propertyName));
        }

        // Grep the @var tag content (type & summary)
        if (!preg_match('#@var\h+(\H+)\h*(.*)\v#', $property->getDocComment(), $metadata)) {
            $message = 'Malformed Boomgo metadata for @var tag in "%s->%s" expects minimum standard declaration "@var [type]"';
            throw new \RuntimeException(sprintf($message, $className , $propertyName));
        }
        array_shift($metadata);
        $metadata = array_filter($metadata);
        return $metadata;
    }

    public function getEmbedDefinition($type, $summary)
    {
        $namespacePattern = '#((?:\\\\*)(?:\w+\\\\*)+\w+)#';
        $embedDefinition = null;
        /*
         * Embedded collection (primitive type)
         *   @var array Valid\Namespace
         */
        if ($type == 'array') {
            if (isset($summary) && preg_match_all($namespacePattern, $summary, $classes)) {
                $embedDefinition = array('array', $classes[0][0]);
            } else {
                $embedDefinition = null; // Assuming we deal with regular array
            }
        }

        /*
         * Embedded object/document (sugar/not typed)
         *   @var object Valid\Namespace
         */
        elseif ($type == 'object') {
            if (preg_match($namespacePattern, $summary, $classes)) {
                $embedDefinition = $classes[0];
            } else {
                throw new \RuntimeExcpetion(sprintf('Malformed Boomgo "object" metadata for @var in "%s->%s"', $className, $propertyName));
            }
        }

        /*
         * Embedded object/document (typed)
         *   @var Valid\Namespace
         */
        elseif (preg_match($namespacePattern, $type, $classes)) {
            $embedDefinition = $classes[0];
        }

        return $embedDefinition;
    }

    /**
     * Check if the getter is public and has no required argument.
     *
     * @param  ReflectionMethod $method the method to check
     * @return Boolean True if the getter is valid
     */
    private function isValidAccessor(\ReflectionMethod $method)
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
    private function isValidMutator(\ReflectionMethod $method)
    {
        return ($method->isPublic() &&
                1 === $method->getNumberOfRequiredParameters());
    }
}