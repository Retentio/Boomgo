<?php

namespace Boomgo\Parser;

class AnnotationParser implements ParserInterface
{
    private $annotation;

    /**
     * Initialize
     * @param string $annotation [description]
     */
    public function __construct($annotation = '@Boomgo')
    {
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
     * @param  ReflectionClass $class
     * @return array
     */
    public function getMap(\ReflectionClass $reflectedClass)
    {
        $map = array();

        $reflectedProperties = $reflectedClass->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {
            if ($this->parser->isBoomgoProperty($reflectedProperty)) {

                $attributeName = $reflectedProperty->getName();
                $keyName = $this->formatter->toMongoKey($attributeName);

                if (!$reflectedProperty->isPublic()) {
                    $accessorName = 'get'.ucfirst($attributeName);
                    $mutatorName = 'set'.ucfirst($attributeName);

                    if (!$reflectedObject->hasMethod($accessorName) ||
                        !$reflectedObject->hasMethod($mutatorName)) {
                        throw new \RuntimeException('Missing accessor/mutator for a private Boomgo property :'.$attributeName);
                    }
                        
                    $reflectedAccessor = $reflectedObject->getMethod($accessorName);
                    $reflectedMutator = $reflectedObject->getMethod($mutatorName);

                    if (!$this->parser->isValidAccessor($reflectedAccessor) ||
                        !$this->parser->isValidMutator($reflectedMutator)) {
                        throw new \RuntimeException('Invalid accessor/mutator for a private Boomgo property :'.$attributeName);
                    }
                }

                $map[$keyName] = null;

                $metadata = $this->parseMetadata($reflectedProperty);

                if (!empty($metadata)) {
                    $map[$keyName] = array('FQDN' => $metadata[1], $this->getMap($metadata[1]));
                }
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