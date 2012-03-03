<?php

namespace Boomgo\Tests\Units\Fixture\Annoted;


class DocumentEmbed
{
    /**
     * @Persistent
     * @var string
     */
    private $string;

    /**
     * @Persistent
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