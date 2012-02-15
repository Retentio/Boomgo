<?php

namespace Boomgo\tests\units\Mock;

/**
 * A valid Boomgo document class
 * fully exposing mapper capabilities with native identifier
 */
class DocumentNativeMongoId
{
    /**
     * Identifier
     * @Boomgo native \MongoId
     */
    private $id;

    /**
     * A mongo stored string
     * @Boomgo
     */
    private $mongoString;

    /**
     * A mongo number
     * @Boomgo 
     */
    private $mongoNumber;

    /**
     * a non persisted property
     */
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
}