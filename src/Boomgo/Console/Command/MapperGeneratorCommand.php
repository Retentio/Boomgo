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

namespace Boomgo\Console\Command;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;
use Boomgo\Builder\MapBuilder,
    Boomgo\Builder\MapperGenerator;
use TwigGenerator\Builder\Generator;

/**
 * Mapper Generator Command
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class MapperGeneratorCommand extends Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->setDescription('Mapper generator command');
        $this->setHelp('boomgo:generate Generate mapper');
        $this->addArgument('source', InputArgument::REQUIRED, 'Define the source directory or file');
        $this->addArgument('namespace', InputArgument::REQUIRED, 'Define the mapper namespace');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Define the mappers directory');
        $this->addArgument('formatter', InputArgument::OPTIONAL, 'Define the formatter', 'Underscore2CamelFormatter');
        $this->addArgument('parser', InputArgument::OPTIONAL, 'Define the parser', 'AnnotationParser');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatterClass = '\\Boomgo\\Formatter\\'.$input->getArgument('formatter');
        $parserClass = '\\Boomgo\\Parser\\'.$input->getArgument('parser');
        $formatter = new $formatterClass;
        $parser = new $parserClass;

        $mapBuilder = new MapBuilder($parser, $formatter);
        $twigGenerator = new Generator();
        $mapperGenerator = new MapperGenerator($mapBuilder, $twigGenerator);
        $mapperGenerator->generate($input->getArgument('source'), $input->getArgument('directory'),  $input->getArgument('namespace'));
    }
}