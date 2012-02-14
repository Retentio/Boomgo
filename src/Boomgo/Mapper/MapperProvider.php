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

/**
 * MapperProvider
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
abstract class MapperProvider
{
    /**
     * Create an instance from a Reflected class
     *
     * @param  ReflectionClass $reflectedClass
     * @throws RuntimeException If constructor requires parameter
     * @return mixed
     */
    protected function createInstance(\ReflectionClass $reflectedClass)
    {
        $constructor = $reflectedClass->getConstructor();

        if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new \RuntimeException('Unable to hydrate an object requiring constructor param');
        }
        return $reflectedClass->newInstance();
    }

    /**
     * Normalize php data for mongo
     *
     * This code chunk was inspired by the Symfony framework
     * and is subject to the MIT license. Please see the LICENCE
     * at https://github.com/symfony/symfony
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     * @author Nils Adermann <naderman@naderman.de>
     *
     * @param  mixed $data
     * @return mixed
     */
    protected function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            return $data;
        }
        if (is_object($data)) {
            return $this->toArray($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->normalize($val);
            }

            return $data;
        }
        throw new \RuntimeException('An unexpected value could not be normalized: '.var_export($data, true));
    }
}