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

    public function getMappedClassName()
    {
        $array = explode('\\', $this->mappedClass);
        return $array[count($array)-1];
    }

    public function getMappedNamespace()
    {
        $array = explode('\\', $this->mappedClass);
        unset($array[count($array)-1]);
        return implode('\\', $array);
    }

    public function getMutator()
    {
        return $this->mutator;
    }

    public function setMutator($value)
    {
        $this->mutator = $value;
    }

    public function getAccessor()
    {
        return $this->accessor;
    }

    public function setAccessor($value)
    {
        $this->accessor = $value;
    }

    public function isComposite()
    {
        return (isset(static::$supportedTypes[$this->type]) && static::$supportedTypes[$this->type] == 'composite');
    }

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

        if ($isSupported && 'composite' === static::$supportedTypes[$type]
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
        $this->accessor = $data['accessor'];
        $this->mutator = $data['mutator'];
    }

    public function toArray()
    {
        $array = array();
        $array['attribute'] = $this->attribute;
        $array['key'] = $this->key;
        $array['type'] = $this->type;
        $array['mutator'] = $this->mutator;
        $array['accessor'] = $this->accessor;

        if ($this->isMapped()) {
            $array['mapped']['type'] = $this->mappedType;
            $array['mapped']['class'] = $this->mappedClass;

            if ($this->isUserMapped()) {
                $array['mapped']['user'] = true;
            }
        }

        return $array;
    }
}