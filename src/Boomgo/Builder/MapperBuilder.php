<?php

namespace Boomgo\Builder;

use TwigGenerator\Builder\BaseBuilder;

class MapperBuilder extends BaseBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultTemplateName()
    {
        return 'Mapper' . self::TWIG_EXTENSION;
    }
}