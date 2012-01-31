<?php

namespace Boomgo\Cache;

class FileCache
{
    private $directory;

    public function __construct($directory = null)
    {
        $this->setDirectory($directory);
    }

    public function setDirectory($directory)
    {
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \InvalidArgumentException('Directory must be valid and writable');
        }

        $this->directory = $directory;
    }

    public function fetch($key)
    {
        $filename = $this->directory.DIRECTORY_SEPARATOR.$key;

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unserialize(file_get_contents($this->directory.DIRECTORY_SEPARATOR.$key, 'r'));
    }

    public function save($key,$data)
    {
        $file = fopen($this->directory.DIRECTORY_SEPARATOR.$key, 'w');
        fwrite($file, serialize($data));
        fclose($file);
    }
}