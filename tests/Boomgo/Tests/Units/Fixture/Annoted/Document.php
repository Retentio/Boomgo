<?php

namespace Boomgo\Tests\Units\Fixture\Annoted;

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
     * @var number
     */
    private $number;

    /**
     * @Persistent
     * @var array
     */
    private $array;

    /**
     * An single embedded DocumentEmbed
     *
     * @Persistent
     * @var Boomgo\Tests\Units\Fixture\Annoted\DocumentEmbed
     */
    private $document;

    /**
     * A embedded collection of DocumentEmbed
     *
     * @Persistent
     * @var array [Boomgo\Tests\Units\Fixture\Annoted\DocumentEmbed]
     */
    private $collection;


    private $attribute;

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

    public function setNumber($value)
    {
        $this->umber = $value;
    }

    public function getNumber()
    {
        return $this->umber;
    }

    public function setAttribute($value)
    {
        $this->attribute = $value;
    }

    public function getAttribute()
    {
        return $this->attribute;
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

    public function setCollection($value)
    {
        $this->collection = $value;
    }

    public function getCollection()
    {
        return $this->collection;
    }
}