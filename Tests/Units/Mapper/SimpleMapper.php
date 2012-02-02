<?php

namespace Boomgo\tests\units\Mapper;

use Boomgo\Mapper;
use Boomgo\Formatter;

use Boomgo\tests\units\Mock;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Mapper/MapperInterface.php';
include __DIR__.'/../../../Mapper/SimpleMapper.php';

include __DIR__.'/../../../Formatter/FormatterInterface.php';

include __DIR__.'/../Mock/Document.php';
include __DIR__.'/../Mock/Formatter.php';

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