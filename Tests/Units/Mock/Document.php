<?php

namespace Boomgo\tests\units\Mock;

/**
 * A valid Boomgo document class
 * fully exposing mapper capabilities with identifier
 */
class Document
{
    /**
     * Identifier
     * @Mongo
     */
    private $id;

    /**
     * A mongo stored string
     * @Mongo
     */
    private $mongoString;

    /**
     * A mongo number
     * @Mongo
     */
    private $mongoNumber;

    /**
     * An single embedded EmbedDocument 
     * @Mongo Document Boomgo\tests\units\Mock\EmbedDocument
     */
    private $mongoDocument;

    /**
     * A embedded collection of Document
     * @Mongo Collection Boomgo\tests\units\Mock\Document
     */
    private $mongoCollection;

    /**
     * A embedded collection of EmbedDocument
     * @Mongo Collection Boomgo\tests\units\Mock\EmbedDocument
     */
    private $mongoCollectionEmbed;

    /**
     * An embedded array 
     * @Mongo
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
    public function setMongoCollectionEmbed($value)
    {
        $this->mongoCollectionEmbed = $value;
    }
    public function getMongoCollectionEmbed()
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
     * @Mongo
     */
    private $mongoString;

    /**
     * A mongo number
     * @Mongo
     */
    private $mongoNumber;

    /**
     * An embedded array 
     * @Mongo
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
     * @Mongo
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
     * @Mongo
     */
    private $id;

    public function setId($id)
    {
        return $this->id;
    }
}

/**
 * A valid Boomgo document class
 * Appear using identifier yet do not defined @Mongo
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
     * @Mongo
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
     * @Mongo
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
     * @Mongo @Mongo
     */
    private $inline;

    /**
     * Invalid annotation multi lines
     * @Mongo 
     * @Mongo
     */
    private $multiline;

    /**
     * Incomplete annotation
     * @Mongo document
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