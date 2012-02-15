<?php

/**
 * This file is part of the Boomgo PHP ODM.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\tests\units\Mapper;

use Boomgo\Mapper;
use Boomgo\Formatter;

use Boomgo\tests\units\Mock;

/**
 * SimpleMapper tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class SimpleMapper extends \mageekguy\atoum\test
{
    public function arrayProvider()
    {
        $embedArray = array('mongoString' => 'an embed string',
            'mongoNumber' => 2,
            'mongoArray' => array('an' => 'embed array', 7 => 2),
            'schemalessKey' => 'an embed schemaless key');

        $embedCollectionArray = array();
        for ($i = 0; $i < 3; $i ++) {
            $embedCollectionArray[] = $embedArray;
        }

        $array =  array('id' => 'an identifier',
            'mongoString' => 'a string',
            'mongoNumber' => 1,
            'mongoDocument' => $embedArray,
            'mongoCollection' => $embedCollectionArray,
            'mongoArray' => array('an' => 'array', 8 => 1),
            'schemalessKey' => 'a schemaless key');

        return $array;
    }

    public function testToHydrate()
    {
        $mapper = new Mapper\SimpleMapper(new Mock\Formatter());
        $array = $this->arrayProvider();

        $object = $mapper->hydrate(new Mock\EmptyDocument(), $array);

        $this->assert
            ->object($object)
            ->isInstanceOf('Boomgo\tests\units\Mock\EmptyDocument');
    }
}