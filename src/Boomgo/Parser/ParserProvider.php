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
use Boomgo\Formatter\FormatterInterface;

/**
 * ParserProvider
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
abstract class ParserProvider
{
    /**
     * Native types supported by MongoDB driver
     * @var array
     */
    static public $nativesClass = array(
        '\\MongoId' => true);

    /**
     * Primitive & pseudo types definition
     * @var array
     */
    static public $types = array(
        'int'     => 'scalar',
        'integer' => 'scalar',
        'bool'    => 'scalar',
        'boolean' => 'scalar',
        'float'   => 'scalar',
        'double'  => 'scalar',
        'real'    => 'scalar',
        'string'  => 'scalar',
        'number'  => 'scalar',
        'mixed'   => 'indefinable',
        'array'   => 'composite',
        'object'  => 'composite');

    /**
     * @var FormatterInterface
     */
    protected $formatter;

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
     * Check if class is natively supported by MongoDB driver
     *
     * Native types (MongoId, MongoDate...)
     * shouldn't be denormalized to an array.
     *
     * @param  string  $class A FQDN
     * @return boolean
     */
    public function isNativeSupported($class)
    {
        // Prepend the firt \ if missing
        $class = (strpos($class,'\\') === 0) ? $class : '\\'.$class;
        return (isset(static::$nativesClass[$class]));
    }

    /**
     * Check if type is composite
     *
     * Array and object could be associated with a submap
     *
     * @param  string  $type A supported (pseudo) type
     * @return boolean
     */
    protected function isCompositeType($type)
    {
        return (!isset(static::$types[$type]) || static::$types[$type] === 'composite');
    }

    /**
     * Manage and update map dependencies
     *
     * Avoid cyclic dependecy: a map embedding the same map.
     * It would cause an infinite parsing loop.
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