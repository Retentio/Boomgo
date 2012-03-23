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

namespace Boomgo\Builder\Generator;

use Boomgo\Builder\MapBuilder;
use Symfony\Component\Finder\Finder;
use TwigGenerator\Builder\Generator as TwigGenerator;

/**
 * AbstractGenerator
 *
 * @author David Guyon <dguyon@gmail.com>
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var Boomgo\Builder\MapBuilder
     */
    private $mapBuilder;

    /**
     * @var TwigGenerator\Builder\Generator
     */
    private $twigGenerator;

    /**
     * Constructor defines MapBuilder & TwigGenerator instance
     *
     * @param MapBuilder    $mapBuilder
     * @param TwigGenerator $twigGenerator
     */
    public function __construct(MapBuilder $mapBuilder, TwigGenerator $twigGenerator)
    {
        $this->setMapBuilder($mapBuilder);
        $this->setTwigGenerator($twigGenerator);
    }

    /**
     * Define the map builder instance
     *
     * @param MapBuilder $mapBuilder
     */
    public function setMapBuilder(MapBuilder $mapBuilder)
    {
        $this->mapBuilder = $mapBuilder;
    }

    /**
     * Return the map builder instance
     *
     * @return MapBuilder
     */
    public function getMapBuilder()
    {
        return $this->mapBuilder;
    }

    /**
     * Define the twig generator instance
     *
     * @param TwigGenerator $twigGenerator
     */
    public function setTwigGenerator(TwigGenerator $twigGenerator)
    {
        $this->twigGenerator = $twigGenerator;
        $this->twigGenerator->setTemplateDirs(array(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Templates'));
        $this->twigGenerator->setMustOverwriteIfExists(true);
    }

    /**
     * Return the twig generator instance
     *
     * @return TwigGenerator
     */
    public function getTwigGenerator()
    {
        return $this->twigGenerator;
    }

    /**
     * Return a collection of files
     *
     * @param mixed  $resources Absolute file or directory path or an array of both.
     * @param string $extension File extension to load/filter with the prefixed dot (.php, .yml).
     *
     * @throws InvalidArgumentException If resources aren't absolute dir or file path
     * 
     * @return array
     */
    public function load($resources, $extension = '')
    {
        $finder = new Finder();
        $collection = array();

        if (is_array($resources)) {
            foreach ($resources as $resource) {
                $subcollection = array();
                $subcollection = $this->load($resource);
                $collection = array_merge($collection, $subcollection);
            }
        } elseif (is_dir($resources)) {
            $files = $finder->files()->name('*'.$extension)->in($resources);
            foreach ($files as $file) {
                $collection[] = realpath($file->getPathName());
            }
        } elseif (is_file($resources)) {
            $collection = array(realpath($resources));
        } else {
            throw new \InvalidArgumentException('Argument must be an absolute directory or a file path or both in an array');
        }

        return $collection;
    }

    /**
     * Callback to change default state of MapBuilder or TwigGenerator
     */
    abstract protected function initialize();
}