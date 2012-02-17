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

            if (!$this->isBoomgoProperty($reflectedProperty)) {
                continue;
            }

            $attributeName = $reflectedProperty->getName();
            $keyName = $this->formatter->toMongoKey($attributeName);
            $accessorName = null;
            $mutatorName = null;
            $embedType = null;
            $embedMap = null;

            // Find accessor & mutator for a protected/private property
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

            // Extract metadata
            $metadata = $this->parseMetadata($reflectedProperty);

            // Handle embedded documents / collections
            if ($this->isCompositeType($metadata['type'])) {
                $definition = $this->getDefinition($metadata);

                if (null !== $definition && !$this->isNativeSupported($definition['class'])) {
                    $embedType = $definition['type'];
                    $embedClass = $definition['class'];
                    $embedMap = $this->buildMap($embedClass, $dependenciesGraph);
                }
            }

            $map->add($keyName, $attributeName, $accessorName, $mutatorName, $embedType, $embedMap);
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
     * Extract metadata from the optional var tag
     *
     * @param  \ReflectionProperty $property
     * @return array
     */
    public function parseMetadata(\ReflectionProperty $property)
    {
        $propertyName = $property->getName();
        $className = $property->getDeclaringClass()->getName();

        // If tag isn't defined: return a default array with the mixed type
        $metadata = array('type' => 'mixed', 'summary' => '');

        $varTag = substr_count($property->getDocComment(), '@var');

        if (1 < $varTag) {
            throw new \RuntimeException(sprintf('@var tag should occur only once for "%s->%s"', $className, $propertyName));
        } elseif (1 === $varTag) {
            // Grep the @var tag content (type & summary)

            if (!preg_match('#@var\h+(\H+)\h*(.*)\v#', $property->getDocComment(), $captured)) {
                $message = 'Malformed Boomgo metadata for @var tag in "%s->%s" expects minimum standard declaration "@var [type]"';
                throw new \RuntimeException(sprintf($message, $className , $propertyName));
            }

            $metadata['type'] = $captured[1];
            $metadata['summary'] = trim($captured[2]);
        }

        return $metadata;
    }

    /**
     * Return a definition from an array of metadata
     *
     * @param  array  $metadata
     * @return array|null
     */
    public function getDefinition(array $metadata)
    {
        $type = $metadata['type'];
        $namespacePattern = '#((?:\\\\*)(?:\w+\\\\*)+\w+)#';
        $definition = null;

        if ($type == 'array'  && isset($metadata['summary'])) {
            // Array are special, could mean an embedded "collection" or a regular array
            if (preg_match_all($namespacePattern, $metadata['summary'], $classes)) {
                // Embedded collection:  @var array Valid\Namespace
                $definition = array('type' => Map::COLLECTION, 'class' => $classes[0][0]);
            }

        } elseif ($type == 'object') {
            if (isset($metadata['summary']) && preg_match($namespacePattern, $metadata['summary'], $classes)) {
                // Embedded & not typed object/document:  @var object Valid\Namespace
                $definition = array('type' => Map::DOCUMENT, 'class' => $classes[0]);
            } else {
                throw new \RuntimeExcpetion(sprintf('Malformed Boomgo "object" metadata for @var in "%s->%s"', $className, $propertyName));
            }

        } elseif (preg_match($namespacePattern, $type, $classes)) {
            //Embedded && typed object/document   @var Valid\Namespace
             $definition = array('type' => Map::DOCUMENT, 'class' => $classes[0]);
        }

        return $definition;
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