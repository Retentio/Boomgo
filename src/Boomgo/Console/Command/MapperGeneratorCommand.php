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
        $this->setHelp('generate:mappers Generate mappers');
        $this->addArgument('mapping-directory', InputArgument::REQUIRED, 'Mapping sources absolute directory path');
        $this->addArgument('models-directory', InputArgument::OPTIONAL, 'Base model/document directory', null);
        $this->addOption('models-namespace', null, InputOption::VALUE_OPTIONAL, 'Model/document namespace (i.e Document or Model)', 'Document');
        $this->addOption('mappers-namespace', null, InputOption::VALUE_OPTIONAL, 'Mappers namespace, default "Mapper"', 'Mapper');
        $this->addOption('parser', null, InputOption::VALUE_OPTIONAL, 'Mapping parser', 'annotation');
        $this->addOption('formatter', null, InputOption::VALUE_OPTIONAL, 'Mapping formatter','Underscore2Camel');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = array();
        $params = array_merge($input->getArguments(), $input->getOptions());

        if (!is_dir($params['mapping-directory'])) {
            throw new \InvalidArgumentException('Invalid mapping sources directory');
        }

        if (null == $params['models-directory']) {
            $params['models-directory'] = $params['mapping-directory'];
        }

        $parserClass = (strpos($params['parser'], '\\') === false) ? '\\Boomgo\\Parser\\'.ucfirst($params['parser']).'Parser' : $params['parser'];
        $formatterClass = (strpos($params['formatter'], '\\') === false) ? '\\Boomgo\\Formatter\\'.ucfirst($params['formatter']).'Formatter' : $params['formatter'];

        $formatter = new $formatterClass;
        $parser = new $parserClass;

        $mapBuilder = new MapBuilder($parser, $formatter);
        $twigGenerator = new Generator();
        $mapperGenerator = new MapperGenerator($mapBuilder, $twigGenerator);

        $mapperGenerator->generate($params['mapping-directory'], $params['models-namespace'], $params['mappers-namespace'], $params['models-directory']);
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
