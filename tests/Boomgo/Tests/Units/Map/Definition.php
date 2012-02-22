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

namespace Boomgo\Tests\Units\Map;

use Boomgo\Tests\Units\Test;
use Boomgo\Map as Src;

/**
 * Definition tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Definition extends Test
{
    public function test__construct()
    {
        // Should throw an error if argument array (metadata) isn't provided
        $this->assert
            ->error(function() {
                new Src\Definition();
            })
            ->withType(E_RECOVERABLE_ERROR);

        // Should set the default type "mixed"
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('mixed');

        // Should set a provided type
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'string'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('string');

        // Should ignore mappedClass when providing a supported non-mappable (pseudo) type
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'string', 'mappedClass' => '\\User\\Namespace'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('string')
            ->variable($definition->getMappedType())
                ->isNull()
            ->variable($definition->getMappedClass())
                ->isNull();

        // Should hanlde a custom type (FQDN): must be defined as a type and a mappedClass
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => '\\User\\Namespace\\Object'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('\\User\\Namespace\\Object')
            ->string($definition->getMappedType())
                ->isEqualTo(Src\Definition::DOCUMENT)
            ->string($definition->getMappedClass())
                ->isEqualTo('\\User\\Namespace\\Object');

        // Should prepend a \ to a custom type (FQDN)
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('\\User\\Namespace\\Object')
            ->string($definition->getMappedClass())
                ->isEqualTo('\\User\\Namespace\\Object');

        // Should handle embedded collection of documents with type array and custom type as mappedClass
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => '\\Valid\\Namespace\\Object'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('array')
            ->string($definition->getMappedType())
                ->isEqualTo(Src\Definition::COLLECTION)
            ->string($definition->getMappedClass())
                ->isEqualTo('\\Valid\\Namespace\\Object');

        // Should prepend a \ for embedded collection of documents
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'Valid\\Namespace\\Object'));
        $this->assert
            ->string($definition->getType())
                ->isEqualTo('array')
            ->string($definition->getMappedClass())
                ->isEqualTo('\\Valid\\Namespace\\Object');

        // Should throw exception if type is not supported and isn't a valid FQDN
        $this->assert
            ->exception(function() {
                new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'invalid_FQDN'));
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('User type "invalid_FQDN" is not a valid FQDN');

        // Should throw exception if type is array and mappedClass isn't a valid FQDN
        $this->assert
            ->exception(function() {
                new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array','mappedClass' => 'invalid_FQDN'));
            })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Mapped class "invalid_FQDN" is not a valid FQDN');
    }

    public function testIsMapped()
    {
        // Should return true if type is a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isMapped())
                ->isTrue();

        // Should return true if type is array and mappedClass is FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isMapped())
                ->isTrue();

        // Should return false if type is supported and non mappable
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'string'));
        $this->assert
            ->boolean($definition->isMapped())
                ->isFalse();

        // Should return false if type is array and no mappedClass is provided
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array'));
        $this->assert
            ->boolean($definition->isMapped())
                ->isFalse();
    }

    public function testIsDocumentMapped()
    {
        // Should return false if type is a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isDocumentMapped())
                ->isTrue();

        foreach (Src\Definition::$nativeClasses as $nativeClass => $boolean) {

            // Should return false if type is a native supported FQDN
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => $nativeClass));
            $this->assert
                ->boolean($definition->isDocumentMapped())
                    ->isTrue();
        }

        // Should return false if type is array and mappedClass is FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isDocumentMapped())
                ->isFalse();

        // Should return false if type is not a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'string'));
        $this->assert
            ->boolean($definition->isDocumentMapped())
                ->isFalse();
    }

    public function testIsCollectionMapped()
    {
        // Should return true if type is array and mappedClass is FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isCollectionMapped())
                ->isTrue();

        foreach (Src\Definition::$nativeClasses as $nativeClass => $boolean) {

            // Should return true if type is array and mappedClass a native supported FQDN
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => $nativeClass));
            $this->assert
                ->boolean($definition->isCollectionMapped())
                    ->isTrue();
        }

        // Should return false if type is a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isCollectionMapped())
                ->isFalse();

        // Should return false if type is not a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array'));
        $this->assert
            ->boolean($definition->isCollectionMapped())
                ->isFalse();
    }

    public function testIsNativeMapped()
    {
        foreach (Src\Definition::$nativeClasses as $nativeClass => $boolean) {

            // Should return true for each native types embedded as single document
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => $nativeClass));
            $this->assert
                ->boolean($definition->isNativeMapped())
                    ->isTrue();

            // Should return true for each native types embedded as collection of documents
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => $nativeClass));
            $this->assert
                ->boolean($definition->isNativeMapped())
                    ->isTrue();
        }

        // Should return false if type isn't a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key'));
        $this->assert
            ->boolean($definition->isNativeMapped())
                ->isFalse();

        // Should return false if type is a FQDN and isn't natively supported
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isNativeMapped())
                ->isFalse();

        // Should return false for non native FQDN type
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isNativeMapped())
                ->isFalse();
    }

    public function testIsUserMapped()
    {
        // Should return true if type is a custom user FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isUserMapped())
                ->isTrue();

        // Should return true if type is array and mappedClass is a custom FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => 'User\\Namespace\\Object'));
        $this->assert
            ->boolean($definition->isUserMapped())
                ->isTrue();

        foreach (Src\Definition::$nativeClasses as $nativeClass => $boolean) {

            // Should return false if type is a native supported FQDN
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => $nativeClass));
            $this->assert
                ->boolean($definition->isUserMapped())
                    ->isFalse();

            // Should return false if type is array and mappedClass a native supported FQDN
            $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'array', 'mappedClass' => $nativeClass));
            $this->assert
                ->boolean($definition->isUserMapped())
                    ->isFalse();
        }

        // Should return false if type isn't a FQDN
        $definition = new Src\Definition(array('attribute' => 'attribute', 'key' => 'key', 'type' => 'string'));
        $this->assert
            ->boolean($definition->isUserMapped())
                ->isFalse();
    }

    public function testIsValidNamespace()
    {
        // Should return true for valid FQDN
        $valid = array('\\Namespace', '\\Another\\NameSpace', 'Another\\Na_me\\Space');
        foreach ($valid as $namespace) {
            $this->assert
            ->boolean(Src\Definition::isValidNamespace($namespace))
                ->isTrue();
        }

        // Should return false for invalid FQDN
        $invalid = array('notnamespace', 'Not \\Namespace', '\\Not Name\\space');
        foreach ($invalid as $namespace) {
            $this->assert
            ->boolean(Src\Definition::isValidNamespace($namespace))
                ->isFalse();
        }
    }
}