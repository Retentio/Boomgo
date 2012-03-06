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
        $this->addArgument('config', InputArgument::REQUIRED, 'Configuration file');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($input->getArgument('config'))) {
            throw new \InvalidArgumentException('Unknown configuration file');
        }

        $config = json_decode(file_get_contents($input->getArgument('config')), true);
        $baseDir = dirname($input->getArgument('config'));

        foreach ($config['mapping'] as $mapping) {
            $parserClass = (strpos($mapping['parser'], '\\') === false) ? '\\Boomgo\\Parser\\'.ucfirst($mapping['parser']).'Parser' : $mapping['parser'];
            $formatterClass = (strpos($mapping['formatter'], '\\') === false) ? '\\Boomgo\\Formatter\\'.ucfirst($mapping['formatter']).'Formatter' : $mapping['formatter'];

            $formatter = new $formatterClass;
            $parser = new $parserClass;

            $mapBuilder = new MapBuilder($parser, $formatter);
            $twigGenerator = new Generator();
            $mapperGenerator = new MapperGenerator($mapBuilder, $twigGenerator);

            if ($parser instanceof \Boomgo\Parser\AnnotationParser) {
                if (!isset($mapping['autoloader'])) {
                    throw new \RuntimeException('Annotation parser require an autoloader');
                }

                $autoloader = ($this->isAbsolutePath($mapping['autoloader'])) ? $mapping['autoloader'] : $baseDir.DIRECTORY_SEPARATOR.$mapping['autoloader'];

                require $autoloader;
            }

            $mapperGenerator->generate($mapping['sources'], $mapping['mappers']['namespace'], $mapping['mappers']['directory']);
        }
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