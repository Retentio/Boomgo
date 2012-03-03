<?php

namespace Boomgo\Tests\Units\Fixture;

class Annotation
{
    /**
     * @var type
     */
    public $nopersistent;

    /**
     * @Persistent
     */
    public $novar;

    /**
     * @Persistent
     * @var type
     */
    public $type;

    /**
     * @Persistent
     * @var type short description
     */
    public $typeDescription;

    /**
     * @Persistent
     * @var Type\Is\Namespace\Object
     */
    public $namespace;

    /**
     * @Persistent
     * @var type [Valid\Namespace\Object]
     */
    public $typeNamespace;

    /**
     * @Persistent
     * @var type [First\Namespace\Object Second\Namespace\Object]
     */
    public $typeManyNamespace;

    /**
     * @Persistent
     * @var type \Namespaced\Object
     */
    public $typeInvalidNamespace;
}