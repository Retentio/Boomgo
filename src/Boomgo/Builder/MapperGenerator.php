<?php

namespace Boomgo\Builder;

use TwigGenerator\Builder\Generator as TwigGenerator;

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
     * @var string
     */
    private $generationPath;


    public function __construct(MapBuilder $mapBuilder, TwigGenerator $twigGenerator, $generationPath)
    {
        $this->setMapBuilder($mapBuilder);
        $this->setTwigGenerator($twigGenerator);
        $this->setGenerationPath($generationPath);
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


    public function setGenerationPath($path)
    {
        $this->generationPath = $path;
    }

    public function getGenerationPath()
    {
        return $this->generationPath;
    }

    public function generate($resources)
    {
        $maps = $this->mapBuilder->build($resources);

        foreach ($maps as $map) {
            $mapperBuilder = new MapperBuilder();
            $mapperBuilder->setOutputName($map->getClassName().'.php');
            $this->twigGenerator->addBuilder($mapperBuilder);
            $mapperBuilder->setVariable('namespace', trim($map->getNamespace(), '\\').'\\Mapper');
            $mapperBuilder->setVariable('imports', array(trim($map->getNamespace(), '\\'), 'Boomgo\\Mapper'));
            $mapperBuilder->setVariable('map', $map);
            $this->twigGenerator->writeOnDisk($this->generationPath);
        }

    }
}