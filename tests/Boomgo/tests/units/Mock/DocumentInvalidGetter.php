<?php

namespace Boomgo\tests\units\Mock;

/**
 * A invalid Boomgo document class
 * using identifier with an invalid accessor (getId)
 */
class DocumentInvalidGetter
{
    /**
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