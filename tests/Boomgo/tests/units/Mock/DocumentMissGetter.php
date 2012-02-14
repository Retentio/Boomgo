<?php

namespace Boomgo\tests\units\Mock;

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