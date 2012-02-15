<?php

namespace Boomgo\tests\units\Mock;

/**
 * A valid Boomgo document class
 * exposing mapper capabilities without identifier
 * (embed document or capped collection)
 */
class EmbedDocument
{
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
     * An embedded array
     * @Boomgo
     */
    private $mongoArray;

    /**
     * A pure php attribute
     * non persisted into mongo
     */
    private $attribute;

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

    public function setMongoArray($value)
    {
        $this->mongoArray = $value;
    }

    public function getMongoArray()
    {
        return $this->mongoArray;
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