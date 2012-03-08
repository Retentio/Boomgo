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
     * Generate mappers
     *
     * @param  string $sources   Mapping source directory
     * @param  string $namespace Base mappers namespace
     * @param  string $directory Base mappers directory
     */
    public function generate($sources, $baseModelsNamespace, $baseMappersNamespace, $baseModelsDirectory)
    {
        $part = str_replace('\\' , DIRECTORY_SEPARATOR, $baseModelsNamespace);

        if (str_replace($part, '', $baseModelsDirectory).$part !== $baseModelsDirectory) {
            throw new \InvalidArgumentException(sprintf('Boomgo support only PSR-O structure, your namespace "%s" doesn\'t reflect your directory structure "%s"', $baseModelsNamespace, $baseModelsDirectory));
        }

        $files = $this->load($sources, '.'.$this->getMapBuilder()->getParser()->getExtension());
        $maps = $this->mapBuilder->build($files);

        foreach ($maps as $map) {
            $modelClassName = $map->getClassName();
            $modelNamespace = trim($map->getNamespace(), '\\');

            if (substr_count($modelNamespace, $baseModelsNamespace) == 0) {
                throw new \RuntimeException(sprintf('The Document map "%s" doesn\'t include the document base namespace "%s"', $map->getClass(), $baseModelsNamespace));
            }

            $modelExtraNamespace = str_replace($baseModelsNamespace, '', strstr($modelNamespace, $baseModelsNamespace));

            $mapperDirectory = str_replace('\\', DIRECTORY_SEPARATOR, str_replace($baseModelsNamespace, $baseMappersNamespace, $baseModelsDirectory.$modelExtraNamespace));
            $mapperClassName = $modelClassName.'Mapper';
            $mapperFileName = $mapperClassName.'.php';

            $mapperBuilder = new MapperBuilder($baseModelsNamespace, $baseMappersNamespace);

            $this->twigGenerator->addBuilder($mapperBuilder);

            $mapperBuilder->setOutputName($mapperFileName);
            $mapperBuilder->setVariable('mappersNamespace', $baseMappersNamespace);
            $mapperBuilder->setVariable('modelsNamespace', $baseModelsNamespace);
            $mapperBuilder->setVariable('namespace', str_replace($baseModelsNamespace, $baseMappersNamespace, $modelNamespace));
            $mapperBuilder->setVariable('className', $mapperClassName);
            $mapperBuilder->setVariable('imports', array($modelNamespace));
            $mapperBuilder->setVariable('map', $map);

            $this->twigGenerator->writeOnDisk($mapperDirectory);
        }
    }

    /**
     * Return a collection of files
     *
     * @param  mixed $resource
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
}