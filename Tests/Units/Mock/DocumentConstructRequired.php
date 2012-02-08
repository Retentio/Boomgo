<?php

namespace Boomgo\tests\units\Mock;

/**
 * A invalid Boomgo document class
 * with a constructor using mandatory param
 */
class DocumentConstructRequired
{
    public function __construct($options) {}
}