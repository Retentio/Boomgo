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

namespace Boomgo\Loader;

use Boomgo\Adapter\FileAdapter;

/**
 * MapperInterface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class FileLoader implements LoaderInterface
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

    public function load($resource)
    {
        $filename = $this->getAbsoluteFilepath($resource);

        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException('Invalid filename or not readable');
        }

        return unserialize(file_get_contents($filename, 'r'));
    }

     /**
     * Return the absolute filename
     *
     * @param  string $identifier The map class name
     * @return string
     */
    protected function getAbsoluteFilepath($filename)
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
    protected function sanitizeFilename($filename)
    {
        $filename = str_replace('\\', '_', $filename);
        $filename = trim($filename);

        return $filename;
    }

}