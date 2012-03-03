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


    public function __construct(MapBuilder $mapBuilder, TwigGenerator $twigGenerator, array $options)
    {
        $this->setMapBuilder($mapBuilder);
        $this->setTwigGenerator($twigGenerator);

        if (!isset($options['namespace']) || !isset($options['namespace']['models']) || !isset($options['namespace']['mappers'])) {
            throw new \InvalidArgumentException('Options "namespace model" and "namespace mapper" must be defined');
        }

        $this->options = $options;
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
     * Get outputname of a Mapper file
     *
     * @param  Map $fqdn Class FQDN
     * @return string
     */
    private function getNames(Map $map)
    {
        $names = array();

        $className = $map->getClassName();
        $namespace = trim(str_replace($this->options['namespace']['models'], $this->options['namespace']['mappers'], $map->getNamespace()), '\\');

        $reflectedClass = new \ReflectionClass($map->getClass());
        $dirName = dirname(str_replace($this->options['namespace']['models'], $this->options['namespace']['mappers'], $reflectedClass->getFileName()));

        $fileName = $className.'Mapper.php';

        return array('className' => $className, 'fileName' => $fileName, 'namespace' => $namespace, 'dirName' => $dirName);
    }

    public function generate($resources)
    {
        $maps = $this->mapBuilder->build($resources);

        foreach ($maps as $map) {
            $names = $this->getNames($map);

            $mapperBuilder = new MapperBuilder();
            $this->twigGenerator->addBuilder($mapperBuilder);
            $mapperBuilder->setOutputName($names['fileName']);
            $mapperBuilder->setVariable('namespace', $names['namespace']);
            $mapperBuilder->setVariable('className', $names['className']);
            $mapperBuilder->setVariable('imports', array(trim($map->getNamespace(), '\\')));
            $mapperBuilder->setVariable('map', $map);
            $this->twigGenerator->writeOnDisk($names['dirName']);
        }

    }
}