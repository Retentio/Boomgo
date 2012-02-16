<?php

namespace Boomgo\tests\units\Mock;

/**
 * An invalid Boomgo document class
 * fully exposing mapper capabilities with identifier
 * yet with cyclic dependency
 */
class Document
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
    private $mongoString;

    /**
     * A mongo stored and public string
     * @Boomgo
     * @var string
     */
    public $mongoPublicString;

    /**
     * A mongo number
     * @Boomgo
     */
    private $mongoNumber;

    /**
     * An single embedded EmbedDocument
     *
     * @Boomgo
     * @var object Boomgo\tests\units\Mock\EmbedDocument
     */
    private $mongoDocument;

    /**
     * A embedded collection of EmbedDocument
     *
     * @Boomgo
     * @var array Boomgo\Tests\Units\Mock\EmbedDocument
     */
    private $mongoCollection;

    /**
     * An embedded array
     * @Boomgo
     * @var array
     */
    private $mongoArray;


    private $attribute;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id =$id;
    }

    public function setMongoString($value)
    {
        $this->mongoString = $value;
    }

    public function getMongoString()
    {
        return $this->mongoString;
    }

    public function setMongoNumber($value)
    {
        $this->mongoNumber = $value;
    }

    public function getMongoNumber()
    {
        return $this->mongoNumber;
    }

    public function setAttribute($value)
    {
        $this->attribute = $value;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function setMongoDocument($value)
    {
        $this->mongoDocument = $value;
    }

    public function getMongoDocument()
    {
        return $this->mongoDocument;
    }

    public function setMongoArray($value)
    {
        $this->mongoArray = $value;
    }

    public function getMongoArray()
    {
        return $this->mongoArray;
    }

    public function setMongoCollection($value)
    {
        $this->mongoCollection = $value;
    }

    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }
}