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
     * @var string Boomgo class annotation tag
     */
    private $globalAnnotation;

    /**
     * @var string Boomgo property annotation tag
     */
    private $localAnnotation;

    /**
     * Constructor
     *
     * @param string $globalTag
     * @param string $localTag
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
     * @see ParserInterface::getExtension()
     *
     * @return string
     */
    public function getExtension()
    {
        return 'php';
    }

    /**
     * ParserInterface implementation
     *
     * @param resource $resource
     * @param string   $type
     *
     * @see ParserInterface::getExtension()
     *
     * @return boolean
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * ParserInterface implementation
     * Extract className and metadata properties
     *
     * @param string $filepath
     *
     * @see ParserInterface::parse()
     *
     * @return array
     */
    public function parse($filepath)
    {
        $metadata = array();

        $reflectedClass = $this->getReflection($filepath);
        $metadata['class'] = $reflectedClass->getName();

        $propertiesMetadata = $this->processPropertiesParsing($reflectedClass);
        $metadata = array_merge($metadata, $propertiesMetadata);
        
        return $metadata;
    }

    /**
     * Extract the fully qualified namespace and return a ReflectionClass object
     * 
     * @param string $filepath Path to the file to parse
     * 
     * @return ReflectionClass
     */    
    protected function getReflection($filepath)
    {
        // Regexp instead of tokenizer because of the bad perf @link > https://gist.github.com/1886076
        if (!preg_match('#^namespace\s+(.+?);.*class\s+(\w+).+;$#sm', file_get_contents($filepath), $captured)) {
            throw new \RuntimeException('Unable to find namespace or class declaration');
        }

        $fqcn = $captured[1].'\\'.$captured[2];

        try {
            $reflectedClass = new \ReflectionClass($fqcn);
        } catch (\ReflectionException $exception) {
            $this->registerAutoload($fqcn, $filepath);
            $reflectedClass = new \ReflectionClass($fqcn);
        }

        return $reflectedClass;
    }

    /**
     * Parse class properties for metadata extraction if valid contains valid annotation local tag
     * 
     * @param  \ReflectionClass $reflectedClass The document reflected object to parse
     * @return array                            An array filled with Definition instance
     */
    protected function processPropertiesParsing(\ReflectionClass $reflectedClass)
    {
        $metadata = array();

        $reflectedProperties = $reflectedClass->getProperties();

        foreach ($reflectedProperties as $reflectedProperty) {
            if ($this->isBoomgoProperty($reflectedProperty)) {
                $propertyMetadata = $this->parseMetadataProperty($reflectedProperty);
                $metadata['definitions'][$reflectedProperty->getName()] = $propertyMetadata;
            }
        }

        return $metadata;
    }

    /**
     * Check if an object property has to be processed by Boomgo
     *
     * @param ReflectionProperty $property the property to check
     *
     * @throws RuntimeException If annotation is malformed
     *
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
     * @param \ReflectionProperty $property
     *
     * @return array
     */
    private function parseMetadataProperty(\ReflectionProperty $property)
    {
        $metadata = array();
        $tag = '@var';
        $docComment = $property->getDocComment();
        $occurence = (int) substr_count($docComment, $tag);

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

    /**
     * Fallback autoloader
     *
     * @param string $fqcn
     * @param string $path
     *
     * @return boolean True if the file has been loaded
     */
    private function registerAutoload($fqcn, $path)
    {
        $namespace = str_replace(strrchr($fqcn, '\\'), '', $fqcn);
        $psr0 = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        // A part of the namespace should match a part of the path (PSR-0 standard)
        if (substr_count($path, $psr0) == 0) {
            throw new \RuntimeException('Boomgo annotation parser support only PSR-0 project structure');
        }

        $baseDirectory = str_replace($psr0, '', dirname($path));
        $partNamespace = explode('\\', $namespace);
        $baseNamespace = $partNamespace[0];

        spl_autoload_register(function($class) use ($baseNamespace, $baseDirectory) {
            if (0 === strpos($class, $baseNamespace)) {
                $path = $baseDirectory.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
                if (!stream_resolve_include_path($path)) {
                    return false;
                }
                require_once $path;

                return true;
            }
        });
    }
}