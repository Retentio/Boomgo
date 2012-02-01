<?php

namespace Boomgo\Mapper;

interface MapperInterface
{
    public function toArray($object);

    public function hydrate($object, array $array);
}