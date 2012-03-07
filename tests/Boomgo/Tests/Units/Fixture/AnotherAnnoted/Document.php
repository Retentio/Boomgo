<?php

namespace Boomgo\Tests\Units\Fixture\AnotherAnnoted;

/**
 * An invalid Boomgo document class
 * fully exposing mapper capabilities with identifier
 * yet with cyclic dependency
 */
class Document
{
    /**
     * @Persistent
     * @var \MongoId
     */
    private $id;

    /**
     * @Persistent
     * @var string
     */
    private $string;

    /**
     * @Persistent
     * @var array
     */
    private $array;

    /**
     * An single embedded DocumentEmbed
     *
     * @Persistent
     * @var Boomgo\Tests\Units\Fixture\AnotherAnnoted\Document
     */
    private $document;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id =$id;
    }

    public function setString($value)
    {
        $this->string = $value;
    }

    public function getString()
    {
        return $this->string;
    }

    public function setArray($value)
    {
        $this->array = $value;
    }

    public function getArray()
    {
        return $this->array;
    }

    public function setDocument($value)
    {
        $this->document = $value;
    }

    public function getDocument()
    {
        return $this->document;
    }
}