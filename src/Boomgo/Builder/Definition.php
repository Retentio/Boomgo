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

namespace Boomgo\Builder;

/**
 * Definition
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Definition
{
    const DOCUMENT = 'document';
    const COLLECTION = 'collection';

    /**
     * @var array Native types supported by MongoDB php extension
     */
    static public $nativeClasses = array(
        '\\MongoId' => true);

    /**
     * @var array Supported primitive & pseudo types
     */
    static public $supportedTypes = array(
        'int'     => 'scalar',
        'integer' => 'scalar',
        'bool'    => 'scalar',
        'boolean' => 'scalar',
        'float'   => 'scalar',
        'double'  => 'scalar',
        'real'    => 'scalar',
        'string'  => 'scalar',
        'number'  => 'scalar',
        'mixed'   => 'composite',
        'array'   => 'composite',
        'object'  => 'composite');

    /**
     * @var string Php attribute name
     */
    private $attribute;

    /**
     * @var string Document key name
     */
    private $key;

    /**
     * @var string Php (pseudo) type (string, number)
     */
    private $type;

    /**
     * @var string Embedded type: document/one or collection/many
     */
    private $mappedType;

    /**
     * @var string FQDN of the mapped/embedded class
     */
    private $mappedClass;

    /**
     * @var string Php mutator (setter) name
     */
    private $mutator;

    /**
     * @var string Php accessor (getter) name
     */
    private $accessor;

    /**
     * Check is a string is a valid namespace
     *
     * @param string  $string
     *
     * @return boolean
     *
     * @todo refacto & move this method
     */
    static public function isValidNamespace($string)
    {
        return (bool) preg_match('#^(?=(?:\w|\\\\)*\\\\+(?:\w|\\\\)*)(?:(?:\w|\\\\)+)$#', $string);
    }

    /**
     * Construct the definition from a metadata array
     *
     * @param array $metadata
     */
    public function __construct(array $metadata)
    {
        $this->fromArray($metadata);
    }

    /**
     * Return the php attribue name for this definition
     *
     * @return string
     */
    public function __toString()
    {
        return $this->attribute;
    }

    /**
     * Return the (pseudo) type (string, number)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the php attribute name
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Return the document key name
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Return the php mutator (setter) name
     *
     * @return string
     */
    public function getMutator()
    {
        return $this->mutator;
    }

    /**
     * Return the php accessor (getter) name
     *
     * @return string
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * Check whether a type is composite (i.e. non-scalar)
     *
     * @return boolean
     */
    public function isComposite()
    {
        return (isset(static::$supportedTypes[$this->type]) && static::$supportedTypes[$this->type] == 'composite');
    }

    /**
     * Check whether this definition is mapped to a class
     *
     * Return true if the mapping is an embedded collection/document
     *
     * @return boolean
     */
    public function isMapped()
    {
        return (bool) $this->mappedClass;
    }

    /**
     * Return the mapped type (collection/many or document/one)
     *
     * @return string
     */
    public function getMappedType()
    {
        return $this->mappedType;
    }

    /**
     * Return the mapped class FQDN
     *
     * @return string
     */
    public function getMappedClass()
    {
        return $this->mappedClass;
    }

    /**
     * Return the mapped short class name
     *
     * @return string
     */
    public function getMappedClassName()
    {
        $array = explode('\\', $this->mappedClass);

        return $array[count($array)-1];
    }

    /**
     * Return the mapped namespace without the short class name
     *
     * @return string
     */
    public function getMappedNamespace()
    {
        $array = explode('\\', $this->mappedClass);
        unset($array[count($array)-1]);

        return implode('\\', $array);
    }

    /**
     * Check if the embedded definition handle a single document
     *
     * @return boolean
     */
    public function isDocumentMapped()
    {
        return Definition::DOCUMENT == $this->mappedType;
    }

    /**
     * Check if the embedded definition handle many documents
     *
     * @return boolean
     */
    public function isCollectionMapped()
    {
        return Definition::COLLECTION == $this->mappedType;
    }

    /**
     * Check whether a type is natively supported by the MongoDB php extension
     *
     * @return boolean
     */
    public function isNativeMapped()
    {
        return $this->isMapped() && isset(static::$nativeClasses[$this->mappedClass]);
    }

    /**
     * Check whether the embedded definition is mapped to an "user" class
     *
     * @return boolean
     */
    public function isUserMapped()
    {
        return $this->isMapped() && !isset(static::$nativeClasses[$this->mappedClass]);
    }

    /**
     * Import an array of metadata
     *
     * @param array $metadata
     *
     * @return void
     */
    private function fromArray(array $metadata)
    {
        // @TODO Throw exception if mandatory parameters Attribute & Key are missing !!

        $defaults = array('type' => 'mixed', 'mappedClass' => null);
        $data = array_merge($defaults, $metadata);

        $type = $data['type'];
        $mappedClass = $data['mappedClass'];
        $isSupported = isset(static::$supportedTypes[$type]);

        if ($isSupported && 'composite' === static::$supportedTypes[$type]
         && !empty($mappedClass)) {
            // Embedded collection: type => array, mappedClass => FQDN

            if (!static::isValidNamespace($mappedClass)) {
                throw new \InvalidArgumentException(sprintf('Mapped class "%s" is not a valid FQDN', $mappedClass));
            }

            // Set the mapping type & prepend a \ on the mapped FQDN
            $this->mappedType = Definition::COLLECTION;
            $this->mappedClass = (strpos($mappedClass, '\\') === 0) ? $mappedClass : '\\'.$mappedClass;

        } elseif (!$isSupported) {
            // Embedded document: type => FQDN

            if (!static::isValidNamespace($type)) {
                throw new \InvalidArgumentException(sprintf('User type "%s" is not a valid FQDN', $type));
            }

            // Set the mapping type & prepend a \ on the mapped FQDN
            $this->mappedType = Definition::DOCUMENT;
            $this->mappedClass = $type = (strpos($type, '\\') === 0) ? $type : '\\'.$type;
        }

        $this->type = $type;
        $this->attribute = $data['attribute'];
        $this->key = $data['key'];
        $this->accessor = $data['accessor'];
        $this->mutator = $data['mutator'];
    }
}