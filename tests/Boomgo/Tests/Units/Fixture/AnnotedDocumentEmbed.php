<?php

namespace Boomgo\Tests\Units\Fixture;


class AnnotedDocumentEmbed
{
    /**
     * A mongo stored string
     * @Boomgo
     * @var string
     */
    private $string;

    /**
     * An embedded array
     * @Boomgo
     * @var array
     */
    private $array;

    public function setString($value)
    {
        $this->string = $value;
    }

    public function getString()
    {
        return $this->string;
    }

    public function setArray($value)
    {
        $this->array = $value;
    }

    public function getArray()
    {
        return $this->array;
    }
}