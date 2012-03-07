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
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
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
        $this->setHelp('boomgo:generate-mapper Generate mappers');
        $this->addArgument('sources-directory', InputArgument::REQUIRED, 'Sources directory');
        $this->addArgument('mappers-namespace', InputArgument::REQUIRED, 'Mappers namespace');
        $this->addArgument('mappers-directory', InputArgument::OPTIONAL, 'Mappers directory, default aside of the source directory', null);
        $this->addOption('parser', 'p', InputOption::VALUE_OPTIONAL, 'Mapping parser', 'annotation');
        $this->addOption('formatter', 'f', InputOption::VALUE_OPTIONAL, 'Mapping formatter','Underscore2Camel');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = array();
        $params = array_merge($input->getArguments(), $input->getOptions());

        if (!is_dir($params['sources-directory'])) {
            throw new \InvalidArgumentException('Invalid sources directory');
        }

        $parserClass = (strpos($params['parser'], '\\') === false) ? '\\Boomgo\\Parser\\'.ucfirst($params['parser']).'Parser' : $params['parser'];
        $formatterClass = (strpos($params['formatter'], '\\') === false) ? '\\Boomgo\\Formatter\\'.ucfirst($params['formatter']).'Formatter' : $params['formatter'];

        $formatter = new $formatterClass;
        $parser = new $parserClass;

        $mapBuilder = new MapBuilder($parser, $formatter);
        $twigGenerator = new Generator();
        $mapperGenerator = new MapperGenerator($mapBuilder, $twigGenerator);

        if (null === $params['mappers-directory']) {
            $params['mappers-directory'] = str_replace(strrchr($params['sources-directory'], DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR.'Mappers', $params['sources-directory']);
        }

        $mapperGenerator->generate($params['sources-directory'], $params['mappers-namespace'], $params['mappers-directory']);
    }

    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
