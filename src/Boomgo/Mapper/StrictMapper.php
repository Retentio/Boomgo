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

namespace Boomgo\Mapper;

use Boomgo\Map;

/**
 * StrictMapper
 *
 * Break schemaless mongo feature,
 * data processing is only ruled by a Map definition.
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class StrictMapper extends MapperProvider implements MapperInterface
{
    /**
     * Convert this object to a Mongo-able array (without native type linerization)
     *
     * @param  object  $object  An object to convert.
     * @return Array
     */
    public function serialize(Map $map, $object)
    {
        $array = array();
        $definitions = $map->getDefinitions();

        foreach ($definitions as $attribute => $definition) {
            $value = null;
            $value = call_user_func(array($object, $definition['accessor']));

            // Recursively normalize nested non-scalar data
            if (null !== $value) {
                if (!is_scalar($value)) {
                    if (isset($definition['mapped'])) {
                        $value = $this->serializeEmbed($map, $definition, $value);
                    } else {
                        $value = $this->normalize($value);
                    }
                }

                $array[$definition['key']] = $value;
            }
        }

        return $array;
    }

    /**
     * Hydrate an object
     *
     * @param  string $object A full qualified domain name or an object
     * @param  array  $array An array of data from mongo
     * @return object
     */
    public function hydrate(Map $map, $object, array $array)
    {
        foreach ($array as $key => $value) {
            if (null !== $value && $map->hasDefinition($key)) {
                $definition = $map->getDefinition($key);
                $attribute = $definition['attribute'];

                if (isset($definition['mapped'])) {
                    $value = $this->hydrateEmbed($map, $definition, $value);
                }

                call_user_func(array($object, $definition['mutator']), $value);
            }
        }

        return $object;
    }

    /**
     * Serialize embed document from a super Map
     * @param  Map    $map
     * @param  array  $definition
     * @param  mixed  $value
     * @return mixed
     */
    protected function serializeEmbed(Map $map, array $definition, $value)
    {
        // No processing on Mongo native type
        if (!isset($definition['mapped']['user'])) {
            return $value;
        }

        // Embed type: single or collection
        $embedType = $definition['mapped']['type'];

        // Load embed map
        $embedMap = $this->loadMap($definition['mapped']['class'], $map);

        if ($embedType == 'document') {
            // Expect an hash (associative array), @todo maybe remove this check ?
            if (!is_object($value)) {
                throw new \RuntimeException('Attribute "'.$definition['attribute'].'" defines an embedded document and expects an associative array of value');
            }

            $value = $this->toArray($embedMap, $value);

        } elseif ($embedType == 'collection') {
            // Expect an array (numeric array), @todo maybe remove this check ?
            if (!is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
                throw new \RuntimeException('Key "'.$definition['attribute'].'" defines an embedded collection and expects an numeric indexed array of value');
            }

            $collection = array();

            // Recursively serialize embed documents
            foreach ($value as $embedValue) {
               $collection[] = $this->toArray($embedMap, $embedValue);
            }

            $value = $collection;
        }

        return $value;
    }

    /**
     * Hydrate embed documents from a super Map
     *
     * @param  Map    $map    The super map
     * @param  string $key    The key defined as embedding doc
     * @param  mixed  $value  The embed data
     * @return mixed
     */
    protected function hydrateEmbed(Map $map, array $definition, $value)
    {
        // No processing on Mongo native type
        if (!isset($definition['mapped']['user'])) {
            return $value;
        }

        // From here, we expect an array of value (embed document or collection)
        if (!is_array($value)) {
            throw new \RuntimeException('Attribute "'.$definition['attribute'].'" defines an embedded document or collection and expects an array of values');
        }

        // Embed type single or collection
        $embedType = $definition['mapped']['type'];

        // Load embed map
        $embedMap = $this->loadMap($definition['mapped']['class']);

        if ($embedType == 'document') {
            // Expect an hash (associative array), @todo maybe remove this check ?
            if (array_keys($value) === range(0, count($value) - 1)) {
                throw new \RuntimeException('Attribute "'.$definition['attribute'].'" defines an embedded document and expects an associative array of values');
            }

            $value = $this->hydrate($embedMap->getClass(), $value);

        } elseif ($embedType == 'collection') {
            // Expect an array (numeric array), @todo maybe remove this check ?
            if (array_keys($value) !== range(0, count($value) - 1)) {
                throw new \RuntimeException('Key "'.$definition['attribute'].'" defines an embedded collection and expects an numeric indexed array of values');
            }

            $collection = array();

            // Recursively hydrate embed documents
            foreach ($value as $embedValue) {
               $collection[] = $this->hydrate($embedMap->getClass(), $embedValue);
            }

            $value = $collection;
        }

        return $value;
    }
}