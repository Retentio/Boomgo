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
    static $primitiveTypes = array('int' => true,
        'integer' => true,
        'bool' => true,
        'boolean' => true,
        'float' => true,
        'double' => true,
        'real' => true,
        'string' => true,
        'array' => true);

    static $pseudoTypes = array('number' => true);

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
                    if ($this->isNativeSupported($embedClass)) {
                        $nativeMapClass = 'Boomgo\\Mapper\\Map'.$embedClass;
                        $embedMap = new $nativeMapClass;
                    } else {
                        $embedMap = $this->buildMap($embedClass, $dependenciesGraph);
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
        $annotationTag = substr_count($property->getDocComment(), $this->getAnnotation());

        if (0 < $annotationTag) {
            if (1 === $annotationTag) {
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
        $tag = substr_count($property->getDocComment(), '@var');
        $metadata = array();

        if (1 === $tag) {
            preg_match('#@var\h+(array|(?>\\\\?[A-Z]{1}[A-Za-z_]+)+)\h*([a-zA-Z\h\\\\]+)*\s*\v*#', $property->getDocComment(), $metadata);
            var_dump($metadata);
            if (count($metadata) > 3) {
                throw new \RuntimeException(sprintf('Malformed metadata for @var tag in "%s->%s" Boomgo expects minimum standard declaration "@var [type]"', $property->getDeclaringClass()->getName() , $property->getName()));
            }

            // Skipping primitive or pseudo type and primitive array without collection definition.
            if (empty($metadata) || ($metadata[1] == 'array' && empty($metadata[2]))) {
                return;
            }

            $type = $metadata[1];
            $summary = (isset($metadata[2])) ? $metadata[2]: null;
        } elseif (0 === $tag) {
            trigger_error(sprintf('Boomgo annoted property in "%s->%s" should be documented with a @var tag', $property->getDeclaringClass()->getName() , $property->getName()), E_USER_WARNING);
        } else {
            throw new \RuntimeException(sprintf('Malformed metadata for @var tag in "%s->%s" Boomgo expects minimum standard declaration "@var [type]"', $property->getDeclaringClass()->getName() , $property->getName()));
        }

        return array($type, $summary);
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