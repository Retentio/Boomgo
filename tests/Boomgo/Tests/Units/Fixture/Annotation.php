<?php

namespace Boomgo\tests\units\Fixture;

class Annotation
{
    /**
     * @Boomgo
     */
    public $novar;

    /**
     * @Boomgo
     * @var type
     */
    public $type;

    /**
     * @Boomgo
     * @var type short description
     */
    public $typeDescription;

    /**
     * @Boomgo
     * @var Type\Is\Namespace\Object
     */
    public $namespace;

    /**
     * @Boomgo
     * @var type [Valid\Namespace\Object]
     */
    public $typeNamespace;

    /**
     * @Boomgo
     * @var type [First\Namespace\Object Second\Namespace\Object]
     */
    public $typeManyNamespace;

    /**
     * @Boomgo
     * @var type \Namespaced\Object
     */
    public $typeInvalidNamespace;
}