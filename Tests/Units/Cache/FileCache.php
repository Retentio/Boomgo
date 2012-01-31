<?php

namespace Boomgo\tests\units\Cache;

use Boomgo\Cache;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';
require_once __DIR__.'/../../../Cache/FileCache.php';

class FileCache extends \mageekguy\atoum\test
{
    private $directory = __DIR__;

    public function clean($filename)
    {
        unlink($filename);
        if (is_file($filename)) {
            throw new \RuntimeException('Test warning : unable to remove file from the test');
        }
    }

    public function testSave()
    {
        $cache = new Cache\FileCache(__DIR__);
        $cache->save('test','my data for the cache test');

        $this->assert
            ->boolean(file_exists($this->directory.DIRECTORY_SEPARATOR.'test'))
            ->isTrue();

        $this->assert
            ->boolean(is_file($this->directory.DIRECTORY_SEPARATOR.'test'))
            ->isTrue();

        $this->assert
            ->boolean(is_readable($this->directory.DIRECTORY_SEPARATOR.'test'))
            ->isTrue();

        $this->assert
            ->string(file_get_contents($this->directory.DIRECTORY_SEPARATOR.'test'))
            ->isEqualTo('s:26:"my data for the cache test";');

        $this->clean($this->directory.DIRECTORY_SEPARATOR.'test');
    }
}