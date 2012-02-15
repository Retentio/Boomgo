<?php

namespace Boomgo\tests\units\Mock;

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