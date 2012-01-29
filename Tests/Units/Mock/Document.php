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
     * @Boomgo Document Boomgo\tests\units\Mock\EmbedDocument
     */
    private $mongoDocument;

    /**
     * A embedded collection of EmbedDocument
     * @Boomgo Collection Boomgo\tests\units\Mock\EmbedDocument
     */
    private $mongoCollection;

    /**
     * An embedded array 
     * @Boomgo
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
        $this->mongoCollectionEmbed = $value;
    }

    public function getMongoCollection()
    {
        return $this->mongoCollectionEmbed;
    }
}

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

/**
 * A invalid Boomgo document class
 * using identifier with a missing mutator (setId)
 */
class DocumentMissSetter
{
    /**
     * Identifier
     * @Boomgo
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with a missing accessor (getId)
 */
class DocumentMissGetter
{
    /**
     * Identifier
     * @Boomgo
     */
    private $id;

    public function setId($id)
    {
        return $this->id;
    }
}

/**
 * A valid Boomgo document class
 * Appear using identifier yet do not defined @Boomgo
 * (the document class must not use mongo identifier)
 */
class DocumentExcludedId
{
    /**
     * Identifier private, non persisted
     */
    private $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with an invalid mutator (setId)
 */
class DocumentInvalidSetter
{
    /**
     * Identifier private, non persisted
     * @Boomgo
     */
    private $id;

    public function setId()
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * using identifier with an invalid accessor (getId)
 */
class DocumentInvalidGetter
{
    /**
     * Identifier private, non persisted
     * @Boomgo
     */
    private $id;

    public function setId()
    {
        $this->id = $id;
    }

    public function getId($id)
    {
        return $this->id;
    }
}

/**
 * A invalid Boomgo document class
 * defining wrong annotation
 */
class DocumentInvalidAnnotation
{
    /**
     * Invalid annotation inline
     * @Boomgo @Boomgo
     */
    private $inline;

    /**
     * Invalid annotation multi lines
     * @Boomgo 
     * @Boomgo
     */
    private $multiline;

    /**
     * Incomplete annotation
     * @Boomgo document
     */
    private $incomplete;
}

/**
 * A valid Boomgo document class
 * with a constructor using optionnal param
 */
class DocummentConstruct
{
    public function __construct($options = array()) {}
}

/**
 * A invalid Boomgo document class
 * with a constructor using mandatory param
 */
class DocummentConstructRequired
{
    public function __construct($options) {}
}

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