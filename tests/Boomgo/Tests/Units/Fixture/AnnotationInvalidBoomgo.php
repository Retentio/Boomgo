<?php

namespace Boomgo\Tests\Units\Fixture;

class AnnotationInvalidBoomgo
{
    /**
     * @Persistent
     * @Persistent
     */
    public $invalidAnnotation;
}