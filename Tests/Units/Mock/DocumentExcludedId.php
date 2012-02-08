<?php

namespace Boomgo\tests\units\Mock;

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