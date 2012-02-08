<?php

namespace Boomgo\tests\units\Mock;

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