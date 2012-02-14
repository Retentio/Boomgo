<?php

namespace Boomgo\tests\units\Mock;

/**
 * An invalid Boomgo document class
 * fully exposing mapper capabilities with identifier
 * yet with cyclic dependency
 */
class DocumentCyclic
{
    /**
     * Identifier
     * @Boomgo
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
     * An single embedded EmbedDocument
     * @Boomgo Document Boomgo\tests\units\Mock\Document
     */
    private $mongoDocument;

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

    public function setMongoDocument($value)
    {
        $this->mongoDocument = $value;
    }

    public function getMongoDocument()
    {
        return $this->mongoDocument;
    }
}