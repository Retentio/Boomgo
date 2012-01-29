<?php

namespace Boomgo\Parser;

use Boomgo\Mapper\Map;
use Boomgo\Formatter\FormatterInterface;

class AnnotationParser implements ParserInterface
{
    private $formatter;

    private $annotation;

    /**
     * Initialize
     * 
     * @param FormmatterInterface $formatter
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
     * Return the data map
     * @param  ReflectionClass $class
     * @return array
     */
    public function getMap($class, $type, $index = null)
    {
        $reflectedClass = new \ReflectionClass($class);

        if (null === $index) { 
            $dependenciesGraph = array();
        }

        if (isset($dependenciesGraph[$reflectedClass->getName()])) {
            throw new \RuntimeException('Cyclic dependency, a document cannot embed (directly/indirectly) itself');
        }

        $dependenciesGraph[$reflectedClass->getName()] = true;

        $map = new Map($class, $type);

        $reflectedProperties = $reflectedClass->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {
            if ($this->isBoomgoProperty($reflectedProperty)) {

                $attributeName = $reflectedProperty->getName();
                $attributePublic = $reflectedProperty->isPublic();
                $keyName = $this->formatter->toMongoKey($attributeName);

                if (!$reflectedProperty->isPublic()) {
                    $accessorName = 'get'.ucfirst($attributeName);
                    $mutatorName = 'set'.ucfirst($attributeName);

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
                
                $subMap = null;
                if (!empty($metadata)) {
                    $subMap = $this->getMap($metadata[1], $metadata[0], $dependenciesGraph);
                }
                // @ Todo mutatorName is not mandatory for public
                $map->add($keyName, $attributeName, $mutatorName, $subMap);
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