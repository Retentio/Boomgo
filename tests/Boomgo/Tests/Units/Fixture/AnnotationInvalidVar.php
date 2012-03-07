<?php

namespace Boomgo\Tests\Units\Fixture;

class AnnotationInvalidVar
{
    /**
     * @Persistent
     * @var
     * @var
     */
    public $invalidVar;
}