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

use Boomgo\Builder\Map;
use Symfony\Component\Finder\Finder;
use TwigGenerator\Builder\Generator as TwigGenerator;

/**
 * MapperGenerator
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class MapperGenerator
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
     * @var array
     */
    private $options;


    public function __construct(MapBuilder $mapBuilder, TwigGenerator $twigGenerator)
    {
        $this->setMapBuilder($mapBuilder);
        $this->setTwigGenerator($twigGenerator);
    }

    public function setMapBuilder(MapBuilder $mapBuilder)
    {
        $this->mapBuilder = $mapBuilder;
    }

    public function getMapBuilder()
    {
        return $this->mapBuilder;
    }

    public function setTwigGenerator(TwigGenerator $twigGenerator)
    {
        $this->twigGenerator = $twigGenerator;
        $this->twigGenerator->setTemplateDirs(array(__DIR__.DIRECTORY_SEPARATOR.'Templates'));
        $this->twigGenerator->setMustOverwriteIfExists(true);
        $this->twigGenerator->setVariables(array('extends' => 'MapperProvider', 'implements' => 'MapperInterface'));
    }

    public function getTwigGenerator()
    {
        return $this->twigGenerator;
    }

    /**
     * Return a collection of resource
     *
     * @param  string $path
     * @return array
     */
    private function load($resources)
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
            $files = $finder->files()->name('*.'.$this->getMapBuilder()->getParser()->getExtension())->in($resources);
            foreach ($files as $file) {
                $collection[] = $file->getPathName();
            }
        } elseif (is_file($resources)) {
            $collection = array($resources);
        } else {
            throw new \InvalidArgumentException('Argument must be an absolute directory or a file path or both in an array');
        }

        return $collection;
    }

    public function generate($sources, $namespace, $directory)
    {
        $files = $this->load($sources);
        $maps = $this->mapBuilder->build($files);

        foreach ($maps as $map) {
            $modelClassName = $map->getClassName();
            $mapperClassName = $modelClassName.'Mapper';
            $mapperFileName = $mapperClassName.'.php';

            $mapperBuilder = new MapperBuilder();
            $this->twigGenerator->addBuilder($mapperBuilder);
            $mapperBuilder->setOutputName($mapperFileName);
            $mapperBuilder->setVariable('namespace', $namespace);
            $mapperBuilder->setVariable('className', $mapperClassName);
            $mapperBuilder->setVariable('imports', array(trim($map->getNamespace(), '\\')));
            $mapperBuilder->setVariable('map', $map);
            $this->twigGenerator->writeOnDisk($directory);
        }

    }
}