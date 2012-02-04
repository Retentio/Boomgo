<?php

namespace Boomgo\Cache;

/**
 * Filesystem cache
 *
 * It will serialize & store the map definition on the disk.
 * You must ensure that the cache directory exists and is writable.
 * (It wont create the cache dir for you.)
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class FileCache implements CacheInterface
{
    private $directory;

    /**
     * Constructor
     *
     * @param mixed $directory string|null An absolute, existing and writable directory
     */
    public function __construct($directory = null)
    {
        if (null === $directory) {
            $directory = __DIR__;
        }

        $this->setDirectory($directory);
    }

    /**
     * Define the cache directory
     *
     * @param string $directory An absolute, existing and writable directory
     */
    public function setDirectory($directory)
    {
        $directory = rtrim($directory,DIRECTORY_SEPARATOR.'\\/');

        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \InvalidArgumentException('Directory must be valid and writable');
        }
        $this->directory = $directory;
    }

    /**
     * Return the absolute path to the cache directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Serialize and write data to the disk
     *
     * @param  string  $identifier The map class name
     * @param  mixed   $data       Data to cache
     * @param  integer $ttl        Time To Live, no effect for this cache system
     * @return boolean $success    True if the file has been cached
     */
    public function save($identifier, $data, $ttl = 0)
    {
        $filename = $this->getAbsoluteFilepath($identifier);

        $file = fopen($filename, 'w');
        $success = fwrite($file, serialize($data));
        fclose($file);

        return (bool)$success;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($identifier)
    {
        $filename = $this->getAbsoluteFilepath($identifier);

        return (is_file($filename) && is_readable($filename));
    }

    /**
     * Unserialize and return data from the disk
     *
     * @param  string $identifier The map class name
     * @return Map
     */
    public function fetch($identifier)
    {
        $filename = $this->getAbsoluteFilepath($identifier);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unserialize(file_get_contents($filename, 'r'));
    }

    /**
     * Remove a cached file from the disk
     *
     * @param  string $identifier The map class name
     * @return boolean
     */
    public function delete($identifier)
    {
        $filename = $this->getAbsoluteFilepath($identifier);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unlink($filename);
    }

    /**
     * Return the absolute filename
     *
     * Since all Map are cached under the FQDN of the related class
     * PHP Namespaced names are formatted to be filesystem compliant
     * Every "\“ are replace by "_", à la zend old convention.
     *
     * @param  string $identifier The map class name
     * @return string
     */
    private function getAbsoluteFilepath($filename)
    {
        return $this->directory.DIRECTORY_SEPARATOR.str_replace('\\', '_', $filename);
    }
}