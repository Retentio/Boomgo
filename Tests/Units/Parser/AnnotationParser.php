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

namespace Boomgo\tests\units\Parser;

use Boomgo\tests\units\Mock;
use Boomgo\Parser;
use Boomgo\Mapper;

require_once __DIR__.'/../../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../../Parser/ParserInterface.php';
include __DIR__.'/../../../Parser/ParserProvider.php';
include __DIR__.'/../../../Parser/AnnotationParser.php';

include __DIR__.'/../../../Formatter/FormatterInterface.php';

include __DIR__.'/../../../Mapper/Map.php';

include __DIR__.'/../Mock/Document.php';
include __DIR__.'/../Mock/Formatter.php';

/**
 * AnnotationParser tests
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class AnnotationParser extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        // Should be able to define the annotation though the constructor
        $parser = new Parser\AnnotationParser(new Mock\Formatter(), '@MyHypeAnnot');

        $this->assert
            ->string($parser->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');
    }

    public function testSetGetAnnotation()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        // Should set and get annotation
        $parser->setAnnotation('@MyHypeAnnot');

        $this->assert
            ->string($parser->getAnnotation())
            ->isIdenticalTo('@MyHypeAnnot');

        // Should throw exception on invalid annotation
        $this->assert
            ->exception(function() use ($parser) {
                $parser->setAnnotation('invalid');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Annotation should start with @ char');

        $this->assert
            ->exception(function() use ($parser) {
                $parser->setAnnotation('@12');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Annotation should start with @ char');
    }


    public function testBuildMap()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        $map = $parser->buildMap('Boomgo\tests\units\Mock\Document');

        // Should return an instance of Map
        $this->assert
            ->object($map)
            ->isInstanceOf('Boomgo\Mapper\Map');

        // Should return an array filled with presisted mongo key name and php attribute name
        $this->assert
            ->array($map->getMongoIndex())
                ->isNotEmpty()
                ->hasSize(7)
                ->isIdenticalTo(array('id' => 'id',
                    'mongoString' => 'mongoString',
                    'mongoPublicString' => 'mongoPublicString',
                    'mongoNumber' => 'mongoNumber',
                    'mongoDocument' => 'mongoDocument',
                    'mongoCollection' => 'mongoCollection',
                    'mongoArray' => 'mongoArray'))
            ->array($map->getPhpIndex())
                ->isNotEmpty()
                ->hasSize(7)
                ->isIdenticalTo(array('id' => 'id',
                    'mongoString' => 'mongoString',
                    'mongoPublicString' => 'mongoPublicString',
                    'mongoNumber' => 'mongoNumber',
                    'mongoDocument' => 'mongoDocument',
                    'mongoCollection' => 'mongoCollection',
                    'mongoArray' => 'mongoArray'));


        // Should return an array of mutator for each mongo key which don't expose a public php attribute
        $this->assert
            ->array($map->getMutators())
            ->isNotEmpty()
            ->hasSize(6)
            ->isIdenticalTo (array('id' => 'setId',
                'mongoString' => 'setMongoString',
                'mongoNumber' => 'setMongoNumber',
                'mongoDocument' => 'setMongoDocument',
                'mongoCollection' => 'setMongoCollection',
                'mongoArray' => 'setMongoArray'));

        // Should return an array where key are mongo key containing embedded document or collection
        $this->assert
            ->array($map->getEmbedMaps())
            ->hasKeys(array('mongoDocument', 'mongoCollection'));

        // Should return a map instance for a single embedded document
        $this->assert
            ->object($map->getEmbedMapFor('mongoDocument'))
            ->isInstanceOf('Boomgo\Mapper\Map');

        // Should return a map instance for a embedded collection
        $this->assert
            ->object($map->getEmbedMapFor('mongoCollection'))
            ->isInstanceOf('Boomgo\Mapper\Map');

        // Should return an array where key are mongo key and value are embed type
        $this->assert
            ->array($map->getEmbedTypes())
            ->isIdenticalTo(array('mongoDocument' => 'DOCUMENT', 'mongoCollection' => 'COLLECTION'));

        // Should return the type 'DOCUMENT' for a key defined as a single embedded document
        $this->assert
            ->string($map->getEmbedTypeFor('mongoDocument'))
            ->isEqualTo('DOCUMENT');

        // Should return the type 'COLLECTION' for a key defined as embedded collection
        $this->assert
            ->string($map->getEmbedTypeFor('mongoCollection'))
            ->isEqualTo('COLLECTION');

    }

/* @todo refactor scope or into a validator class
    public function testHasValidIdentifier()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        // Should return true when object provide a valid identifier implementation
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should return false when an object provide an uncomplete identifier implementation (getter)
        $object = new Mock\DocumentMissGetter();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should return false when an object provide an uncomplete identifier implementation (setter)
        $object = new Mock\DocumentMissSetter();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

         // Should return false when an object provide a valid identifier implementation without mongo annotation
        $object = new Mock\DocumentExcludedId();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->hasValidIdentifier($reflection);

        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should throw exception when an bject provide a complete yet invalid identifier implem (getter)
        $object = new Mock\DocumentInvalidGetter();
        $reflection = new \ReflectionObject($object);

        $this->assert
            ->exception(function() use ($parser, $reflection) {
                    $parser->hasValidIdentifier($reflection);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Object expect an id but do not expose valid accessor/mutator');

        // Should throw exception when an object provide a complete yet invalid identifier implem (setter)
        $object = new Mock\DocumentInvalidSetter();
        $reflection = new \ReflectionObject($object);

        $this->assert
            ->exception(function() use ($parser, $reflection) {
                    $parser->hasValidIdentifier($reflection);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Object expect an id but do not expose valid accessor/mutator');
    }

    public function testIsValidAccessor()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        // Should return true when getter is public and do not required argument
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->isValidAccessor($reflection->getMethod('getId'));

        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should return false when getter is invalid
        $object = new Mock\DocumentInvalidGetter();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->isValidAccessor($reflection->getMethod('getId'));

        $this->assert
            ->boolean($bool)
            ->isFalse();
    }

    public function testIsValidMutator()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        // Should return true when setter is public and require only one argument
        $object = new Mock\Document();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->isValidMutator($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should return false when setter is invalid
        $object = new Mock\DocumentInvalidSetter();
        $reflection = new \ReflectionObject($object);
        $bool = $parser->isValidMutator($reflection->getMethod('setId'));

        $this->assert
            ->boolean($bool)
            ->isFalse();
    }

    public function testIsBoomgoProperty()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

         // Should return false if proprerty don't have annotation
        $document = new Mock\DocumentExcludedId();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $bool = $parser->isBoomgoProperty($reflectedProperty);
        $this->assert
            ->boolean($bool)
            ->isFalse();

        // Should return true if proprerty has annotation
        $document = new Mock\Document();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $bool = $parser->isBoomgoProperty($reflectedProperty);
        $this->assert
            ->boolean($bool)
            ->isTrue();

        // Should throws exception if property has 2 inline annotations
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('inline');
        $this->assert
            ->exception(function() use ($parser, $reflectedProperty) {
                    $parser->isBoomgoProperty($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Boomgo annotation should occur only once');

        // Should throws exception if property has 2 multi-line annotations
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('multiline');
        $this->assert
            ->exception(function() use ($parser, $reflectedProperty) {
                    $parser->isBoomgoProperty($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Boomgo annotation should occur only once');
    }

    public function testParseMetadata()
    {
        $parser = new Parser\AnnotationParser(new Mock\Formatter());

        // Should throw exception if annotation is missing
        $document = new Mock\DocumentExcludedId();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('id');

        $this->assert
            ->exception(function() use ($parser, $reflectedProperty) {
                    $parser->parseMetadata($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Malformed metadata');

        // Should throw exception if annotation is incomplete
        $document = new Mock\DocumentInvalidAnnotation();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('incomplete');

        $this->assert
            ->exception(function() use ($parser, $reflectedProperty) {
                    $parser->parseMetadata($reflectedProperty);
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Malformed metadata');

        // Should return an array of metadata
        $document = new Mock\Document();
        $reflectedObject = new \ReflectionObject($document);
        $reflectedProperty = $reflectedObject->getProperty('mongoDocument');

        $metadata = $parser->parseMetadata($reflectedProperty);
        $this->assert
            ->array($metadata)
            ->isNotEmpty()
            ->hasSize(2)
            ->strictlyContainsValues(array('Document', 'Boomgo\tests\units\Mock\EmbedDocument'));
    }
*/
}