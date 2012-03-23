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

use Boomgo\Builder\TwigMapperBuilder;

/**
 * MapperGenerator
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 * @author David Guyon <dguyon@gmail.com>
 */
class MapperGenerator extends AbstractGenerator
{
    /**
     * Initialize default instance state
     */
    protected function initialize()
    {
        $this->getTwigGenerator()->setVariables(array(
            'extends' => 'MapperProvider',
            'implements' => 'MapperInterface'
        ));
    }

    /**
     * Generate mappers
     *
     * The base models & mappers namespace are just the "namespace fragment"
     * not the full namespace part, i.e. "Document", "Mapper".
     * -"Document" & "Mapper": Project\Domain\Document => Project\Domain\Mapper
     * -"Document" & "Document\Mapper": Project\Domain\Document => Project\Domain\Document\Mapper
     *
     * The Base models directory & base models namespace must match PSR-O.
     * This means: base models namespace fragment must match the end of your base model directory.
     * - "Document" => "/path/to/your/Project/Document".
     * - "Domain\SubDomain\Model" => "/path/to/your/Domain/SubDomain/Model".
     *
     * The generator will write aside of your Document folder/namespace. If you want to change this
     * behavior, you just have to customize the base mapper namespace: "Document\Mapper".
     *
     * @param string $sources              Mapping source directory
     * @param string $baseModelsNamespace  Base models namespace (Document, Model)
     * @param string $baseMappersNamespace Base mappers namespace (Mapper, Mapping)
     * @param string $baseModelsDirectory  Base models directory
     */
    public function generate($sources, $baseModelsNamespace, $baseMappersNamespace, $baseModelsDirectory)
    {
        // Explicit call for MapperGenerator requirements
        $this->initialize();

        $baseModelsNamespace = trim($baseModelsNamespace, '\\');
        $baseMappersNamespace = trim($baseMappersNamespace, '\\');
        $baseModelsDirectory = rtrim($baseModelsDirectory, DIRECTORY_SEPARATOR);

        $part = str_replace('\\', DIRECTORY_SEPARATOR, $baseModelsNamespace);

        if (str_replace($part, '', $baseModelsDirectory).$part !== $baseModelsDirectory) {
            throw new \InvalidArgumentException(sprintf('Boomgo support only PSR-O structure, your namespace "%s" doesn\'t reflect your directory structure "%s"', $baseModelsNamespace, $baseModelsDirectory));
        }

        $files = $this->load($sources, '.'.$this->getMapBuilder()->getParser()->getExtension());
        $maps = $this->getMapBuilder()->build($files);

        foreach ($maps as $map) {
            $modelClassName = $map->getClassName();
            $modelNamespace = trim($map->getNamespace(), '\\');

            if (substr_count($modelNamespace, $baseModelsNamespace) == 0) {
                throw new \RuntimeException(sprintf('The Document map "%s" doesn\'t include the document base namespace "%s"', $map->getClass(), $baseModelsNamespace));
            }

            $modelExtraNamespace = str_replace($baseModelsNamespace, '', strstr($modelNamespace, $baseModelsNamespace));
            $mapperDirectory = str_replace(str_replace('\\', DIRECTORY_SEPARATOR, $baseModelsNamespace), str_replace('\\', DIRECTORY_SEPARATOR, $baseMappersNamespace), str_replace('\\', DIRECTORY_SEPARATOR, $baseModelsDirectory.$modelExtraNamespace));
            $mapperClassName = $modelClassName.'Mapper';
            $mapperFileName = $mapperClassName.'.php';

            $twigMapperBuilder = new TwigMapperBuilder();

            $this->getTwigGenerator()->addBuilder($twigMapperBuilder);

            $twigMapperBuilder->setOutputName($mapperFileName);
            $twigMapperBuilder->setVariable('mappersNamespace', $baseMappersNamespace);
            $twigMapperBuilder->setVariable('modelsNamespace', $baseModelsNamespace);
            $twigMapperBuilder->setVariable('namespace', str_replace($baseModelsNamespace, $baseMappersNamespace, $modelNamespace));
            $twigMapperBuilder->setVariable('className', $mapperClassName);
            $twigMapperBuilder->setVariable('imports', array($modelNamespace));
            $twigMapperBuilder->setVariable('map', $map);

            $this->getTwigGenerator()->writeOnDisk($mapperDirectory);
        }

        return true;
    }
}