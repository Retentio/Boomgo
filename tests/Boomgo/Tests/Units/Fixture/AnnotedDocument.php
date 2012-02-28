<?php

namespace Boomgo\Tests\Units\Fixture;

/**
 * An invalid Boomgo document class
 * fully exposing mapper capabilities with identifier
 * yet with cyclic dependency
 */
class AnnotedDocument
{
    /**
     * Identifier
     * @Boomgo
     * @var \MongoId
     */
    private $id;

    /**
     * A mongo stored string
     * @Boomgo
     * @var string
     */
    private $string;

    /**
     * A mongo number
     * @Boomgo number
     */
    private $number;

    /**
     * An embedded array
     * @Boomgo
     * @var array
     */
    private $array;

    /**
     * An single embedded EmbedDocument
     *
     * @Boomgo
     * @var Boomgo\Tests\Units\Fixture\AnnotedDocumentEmbed
     */
    private $document;

    /**
     * A embedded collection of EmbedDocument
     *
     * @Boomgo
     * @var array [Boomgo\Tests\Units\Fixture\AnnotedDocumentEmbed]
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