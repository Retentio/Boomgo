<?php

namespace Boomgo\Cache;

class FileCache implements CacheInterface
{
    private $directory;

    public function __construct($directory = null)
    {
        if (null === $directory) {
            $directory = __DIR__;
        }

        $this->setDirectory($directory);
    }

    public function setDirectory($directory)
    {
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \InvalidArgumentException('Directory must be valid and writable');
        }
        $this->directory = $directory;
    }

    public function save($identifier, $data, $ttl = 0)
    {
        $filename = $this->formatFilename($identifier);

        $file = fopen($filename, 'w');
        fwrite($file, serialize($data));
        fclose($file);
    }

    public function contains($identifier)
    {
        $filename = $this->formatFilename($identifier);

        return (is_file($filename) && is_readable($filename));
    }

    public function fetch($identifier)
    {
        $filename = $this->formatFilename($identifier);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unserialize(file_get_contents($filename, 'r'));
    }

    public function delete($identifier)
    {
        $filename = $this->formatFilename($identifier);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        unlink($filename);
    }

    private function formatFilename($identifier)
    {
        return $this->directory.DIRECTORY_SEPARATOR.str_replace('\\', '_', $identifier);
    }
}