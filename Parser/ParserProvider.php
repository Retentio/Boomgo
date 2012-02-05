<?php

/**
 * This file is part of the Boomgo PHP ODM.
 *
 * http://boomgo.org
 * https://github.com/Retentio/Boomgo
 *
 * (c) Ludovic Fleury <ludo.fleury@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Boomgo\Parser;

use Boomgo\Mapper\Map;
use Boomgo\Cache\CacheInterface;
use Boomgo\Formatter\FormatterInterface;

/**
 * ParserProvider
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
abstract class ParserProvider
{
    protected $formatter;

    protected $cache;

    /**
     * Initialize
     *
     * @param FormmatterInterface $formatter
     * @param string $annotation
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->setFormatter($formatter);
    }

    /**
     * Define the key/attribute formatter
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Return the key/attribute formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Manage and update map dependencies
     *
     * @param  string $class            Class to add to the depencies list
     * @param  array  $dependeciesGraph Null or dependencie legacy
     * @return array
     */
    protected function updateDependencies($class, $dependenciesGraph)
    {
        if (null === $dependenciesGraph) {
            $dependenciesGraph = array();
        }

        if (isset($dependenciesGraph[$class])) {
            throw new \RuntimeException('Cyclic dependency, a document cannot directly/indirectly be embed in itself');
        }

        $dependenciesGraph[$class] = true;

        return $dependenciesGraph;
    }
}