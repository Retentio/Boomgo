<?php

namespace Boomgo\Map;

class Definition
{
    const DOCUMENT = 'document';
    const COLLECTION = 'collection';

    /**
     * Native types supported by MongoDB driver
     * @var array
     */
    static public $nativeClasses = array(
        '\\MongoId' => true);

    /**
     * Supported primitive & pseudo types
     *
     * @var array
     */
    static public $supportedTypes = array(
        'int'     => false,
        'integer' => false,
        'bool'    => false,
        'boolean' => false,
        'float'   => false,
        'double'  => false,
        'real'    => false,
        'string'  => false,
        'number'  => false,
        'mixed'   => false,
        'array'   => true,
        'object'  => false);

    private $attribute;

    private $key;

    private $type;

    private $mappedType;

    private $mappedClass;

    private $mutator;

    private $accessor;

    /**
     * Check is a string is a valid namespace
     * @todo  refacto & move this method
     *
     * @param  string  $string
     * @return boolean
     */
    static public function isValidNamespace($string)
    {
        return (bool)preg_match('#^(?=(?:\w|\\\\)*\\\\+(?:\w|\\\\)*)(?:(?:\w|\\\\)+)$#', $string);
    }

    public function __construct(array $metadata)
    {
        $this->fromArray($metadata);
    }

    public function __toString()
    {
        return $this->attribute;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
    public function getKey()
    {
        return $this->key;
    }

    public function getMappedType()
    {
        return $this->mappedType;
    }

    public function getMappedClass()
    {
        return $this->mappedClass;
    }

    // public function getMutator()
    // {
    //     return $this->mutator;
    // }

    // public function setMutator($value)
    // {
    //     $this->mutator = $value;
    // }

    // public function getAccessor()
    // {
    //     return $this->accessor;
    // }

    // public function setAccessor($value)
    // {
    //     $this->accessor = $value;
    // }

    public function isMapped()
    {
        return (bool)$this->mappedClass;
    }

    /**
     * Check if the embed definition handle a single document
     *
     * @return boolean
     */
    public function isDocumentMapped()
    {
        return Definition::DOCUMENT == $this->mappedType;
    }

    /**
     * Check if the embed definition handle many documents
     *
     * @return boolean
     */
    public function isCollectionMapped()
    {
        return Definition::COLLECTION == $this->mappedType;
    }

    /**
     * Check if type is natively supported by MongoDB driver
     *
     * @return boolean
     */
    public function isNativeMapped()
    {
        return $this->isMapped() && isset(static::$nativeClasses[$this->mappedClass]);
    }

    /**
     * Check if the embed definition is mapped to an user class
     *
     * @return boolean
     */
    public function isUserMapped()
    {
        return $this->isMapped() && !isset(static::$nativeClasses[$this->mappedClass]);
    }

    private function fromArray(array $metadata)
    {
        // @TODO Throw exception if mandatory parameters Attribute & Key are missing !!

        $defaults = array('type' => 'mixed', 'mappedClass' => null);
        $data = array_merge($defaults, $metadata);

        $type = $data['type'];
        $mappedClass = $data['mappedClass'];
        $isSupported = isset(static::$supportedTypes[$type]);

        if ($isSupported && true === static::$supportedTypes[$type]
         && !empty($mappedClass)) {
            // Embedded collection: type => array, mappedClass => FQDN

            if (!static::isValidNamespace($mappedClass)) {
                throw new \InvalidArgumentException(sprintf('Mapped class "%s" is not a valid FQDN', $mappedClass));
            }

            // Set the mapping type & prepend a \ on the mapped FQDN
            $this->mappedType = Definition::COLLECTION;
            $this->mappedClass = (strpos($mappedClass,'\\') === 0) ? $mappedClass : '\\'.$mappedClass;

        } elseif (!$isSupported) {
            // Embedded document: type => FQDN

            if (!static::isValidNamespace($type)) {
                throw new \InvalidArgumentException(sprintf('User type "%s" is not a valid FQDN', $type));
            }

            // Set the mapping type & prepend a \ on the mapped FQDN
            $this->mappedType = Definition::DOCUMENT;
            $this->mappedClass = $type = (strpos($type,'\\') === 0) ? $type : '\\'.$type;
        }

        $this->type = $type;
        $this->attribute = $data['attribute'];
        $this->key = $data['key'];
    }
}