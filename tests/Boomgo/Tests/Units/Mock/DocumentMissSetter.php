<?php

namespace Boomgo\tests\units\Mock;

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