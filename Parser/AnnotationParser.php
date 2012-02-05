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
                    list($embedType,$embedClass) = $metadata;
                    $embedMap = $this->buildMap($embedClass, $dependenciesGraph);
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
    private function parseMetadata(\ReflectionProperty $property)
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