<?php

/**
 * This file is part of the Boomgo PHP ODM for MongoDB.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Builder;

use TwigGenerator\Builder\BaseBuilder;

/**
 * MapperBuilder
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class MapperBuilder extends BaseBuilder
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDefaultTemplateName()
    {
        return 'Mapper' . self::TWIG_EXTENSION;
    }
}