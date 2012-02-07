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

namespace Boomgo\Cache;

/**
 * Filesystem cache
 *
 * Serialize & store the map definition on the disk.
 * The cache directory must exist and be writable.
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
     * @param  string  $identifier Unique cache identifier
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
     * @param  string $identifier Unique cache identifier
     * @throws InvalidArgumentException If filename is invalid or not readable
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
     * @param  string  $identifier Unique cache identifier
     * @throws InvalidArgumentException If filename is invalid or not readable
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
     * @param  string $identifier The map class name
     * @return string
     */
    private function getAbsoluteFilepath($filename)
    {
        return $this->directory.DIRECTORY_SEPARATOR.$this->sanitizeFilename($filename);
    }

    /**
     * Return a valid filename
     *
     * Since the cache is mostly used to store Map definition,
     * this filters are pretty simple and handle a FQDN as a filename.
     *
     * @param  string $filename
     * @return string
     */
    private function sanitizeFilename($filename)
    {
        $filename = str_replace('\\', '_', $filename);
        $filename = trim($filename);

        return $filename;
    }
}