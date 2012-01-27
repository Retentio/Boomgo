<?php

namespace Boomgo\tests\units;

use Boomgo;
use Boomgo\Parser;

require_once __DIR__.'/../../vendor/mageekguy.atoum.phar';

include __DIR__.'/../../Mapper.php';

include __DIR__.'/../../Parser/ParserInterface.php';
include __DIR__.'/../../Parser/AnnotationParser.php';

include __DIR__.'/../../Formatter/FormatterInterface.php';

include __DIR__.'/Mock/Document.php';
include __DIR__.'/Mock/Formatter.php';

class Mapper extends \mageekguy\atoum\test
{
    public function test__construct()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());
    }

    public function testNormalize()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());

        // Should not alter scalar and null value
        $output = $mapper->normalize(1);
        $this->assert
            ->variable($output)
            ->isEqualTo(1);

        $output = $mapper->normalize(null);
        $this->assert
            ->variable($output)
            ->isNull();

        // Should return an empty array when providing an empty object
        $output = $mapper->normalize(new Mock\Document());
        $this->assert
            ->array($output)
            ->isEmpty();

        // Should return the exact same array when providing an array
        $output = $mapper->normalize(array('yet' => 'another', 'array', 17 => 13));
        $this->assert
            ->array($output)
            ->isIdenticalTo(array('yet' => 'another', 'array', 17 => 13));

        // Should throw an exception when dealing with non-normalizable value
        $file = fopen(__FILE__, 'r');
        $this->assert
            ->exception(function() use ($mapper, $file) {
                    $mapper->normalize($mapper->normalize($file));
                })
            ->isInstanceOf('RuntimeException');

        fclose($file);
    }

    public function testToArray()
    {
        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());

        // Should throw exception if argument is not an object
        $this->assert
            ->exception(function() use ($mapper) {
                    $mapper->toArray(1);;
                })
            ->isInstanceOf('InvalidArgumentException')
            ->hasMessage('Argument must be an object');
        
        // Should return an empty array when providing an empty object
        $array = $mapper->toArray(new \stdClass()); 
        
        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an empty array when providing object without value
        $document = new Mock\Document();
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an array with data defined mongo
        $document = new Mock\EmbedDocument();
        $document->setMongoString('a string');
        $document->setMongoNumber(5);

        $array = $mapper->toArray($document);
        
        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->isIdenticalTo(array('mongoString' => 'a string',
                'mongoNumber' => 5,
                'mongoArray' => null)); 

        // Should return an array with normalized key _id when object has identifier
        $document = new Mock\Document();
        $document->setId('an id');
        $document->setMongoString('a string');
        $document->setMongoNumber(5);

        $array = $mapper->toArray($document);
        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('_id')
            ->notHasKey('id');

        $this->assert
            ->variable($array['_id'])
            ->isIdenticalTo('an id');

        // Should return an empty array when all mongo keys are null
        $document = new Mock\Document();
        $document->setAttribute('an excluded value');
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isEmpty();

        // Should return an array filled with every mongo keys if at least one mongo key is filled.
        $document = new Mock\Document();
        $document->setMongoString('a stored string');
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKeys(array('_id', 
                'mongoString',
                'mongoNumber',
                'mongoDocument',
                'mongoArray'))
            ->notHasKey('attribute');

        // Should recursively normalize single embedded object
        $embed = new Mock\EmbedDocument();
        $embed->setMongoString('a embed stored string');
        $embed->setAttribute('an embed excluded value');
        
        $document = new Mock\Document();
        $document->setMongoDocument($embed);
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongoDocument');
            
        $this->assert
            ->array($array['mongoDocument'])
            ->hasKeys(array('mongoString', 'mongoNumber'))
            ->strictlyContainsValues(array('a embed stored string'));

        // Should recursively include array
        $document = new Mock\Document();
        $document->setMongoArray(array('an' => 'embedded', 'array', 6 => 2));
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongoArray');
            
        $this->assert
            ->array($array['mongoArray'])
            ->isIdenticalTo(array('an' => 'embedded', 'array', 6 => 2));

        // Should recursively normalize embedded collection
        $embedCollection = array();
        for ($i = 0; $i < 10; $i ++) {
            $embedDocument = new Mock\Document();
            $embedDocument->setMongoString('a embed stored string');
            $embedDocument->setAttribute('an embed excluded value');
            $embedCollection[] = $embedDocument;
        }

        $document = new Mock\Document();
        $document->setMongoCollection($embedCollection);
        $array = $mapper->toArray($document);

        $this->assert
            ->array($array)
            ->isNotEmpty()
            ->hasKey('mongoCollection');

        $this->assert
            ->array($array['mongoCollection'])
            ->isNotEmpty()
            ->hasSize(10);

        for ($i = 0; $i < 10; $i ++) {
            $this->assert
                ->array($array['mongoCollection'][$i])
                ->isNotEmpty()
                ->hasKeys(array('mongoString', 'mongoNumber'))
                ->strictlyContainsValues(array('a embed stored string'));
        }
    }

    public function testHydrate()
    {
        $ns = 'Boomgo\\tests\\units\\Mock\\';

        $mapper = new Boomgo\Mapper(new Parser\AnnotationParser(),new Mock\Formatter());
        
        // Sould throw exception if constructor has mandatory prerequesite
        $this->assert
            ->exception(function() use ($mapper,$ns) {
                    $mapper->hydrate($ns.'DocummentConstructRequired', array('_id' => 1));
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Unable to hydrate object requiring constructor param');

        // Should throw exception if object don't have/expose identifer (id, setId)
        $this->assert
            ->exception(function() use ($mapper, $ns) {
                    $mapper->hydrate($ns.'DocummentConstruct', array('_id' => 1));
                })
            ->isInstanceOf('RuntimeException')
            ->hasMessage('Object do not handle identifier');

        // Should hydrate correctly an object
        $array = array('_id' => 'identifier',
            'mongoString' => 'a mongo string',
            'mongoNumber' => 1664,
            'attribute' => 'a parasite attribute'); 

        $object = $mapper->hydrate($ns.'Document', $array);

        $this->assert
            ->object($object)
            ->isInstanceOf('Boomgo\tests\units\Mock\Document');

        $this->assert
            ->variable($object->getId())
            ->isEqualTo('identifier');

        $this->assert
            ->variable($object->getMongoString())
            ->isEqualTo('a mongo string');

        $this->assert
            ->variable($object->getMongoNumber())
            ->isEqualTo(1664);

        $this->assert
            ->variable($object->getAttribute())
            ->isNull();

        // Should hydrate correctly a document containing multiple levels of nested documents, collections and arrays
        // This test emulate a complexe document embedding
        // {
        //      _id:
        //      mongoString: 
        //      mongoDocument:
        //      mongoCollection: 10 x Mock\Document which embed 3 x Mock\EmbedDocument (and mutliples arrays / assoc arrays);
        //      mongoCollectionEmbed: 3 x Mock\EmbedDocument (and 1 array nested in 1 assoc array)
        //      mongoArray: 1 assoc array which embed 1 array
        // }
        $array = array('yet', 'another', 'embed', 'array'); 

        $assocArray = array('an' => 'associative', 8 => 'array', 'containing' => $array);
        
        $collectionEmbedDocument = array();
        for ($j = 0; $j < 3; $j ++) {
            $document = array('mongoString' => 'a EMBED DOCUMENT collection-embed stored string',
                'mongoNumber' => null,
                'mongoArray' => $assocArray);
            
            $collectionEmbedDocument[] = $document;
        } 
        
        $collectionDocument = array();
        for ($i = 0; $i < 10; $i ++) {
            $document = array('_id' => 'identifier'.$i,
                'mongoString' => 'a DOCUMENT collection-embed stored string',
                'mongoNumber' => null,
                'mongoDocument' => null,
                'mongoCollection' => null,
                'mongoCollectionEmbed' => $collectionEmbedDocument,
                'mongoArray' => $assocArray);
            $collectionDocument[] = $document;
        }
        
        $embedDocument = array('mongoString' => 'embed mongo string',
            'mongoNumber' => 1337,
            'mongoArray' => $assocArray); 
        
        $data = array('_id' => 'identifier',
            'mongoString' => 'a mongo string',
            'mongoNumber' => 1664,
            'mongoDocument' => $embedDocument,
            'mongoCollection' => $collectionDocument,
            'mongoCollectionEmbed' => $collectionEmbedDocument,
            'mongoArray' => $array);

        $object = $mapper->hydrate($ns.'Document', $data);

        $this->assert
            ->object($object)
            ->isInstanceOf('Boomgo\tests\units\Mock\Document');

        $this->assert
            ->string($object->getMongoString())
            ->isEqualTo('a mongo string');

        $this->assert
            ->integer($object->getMongoNumber())
            ->isEqualTo(1664);
        
        // check single embedded document (1 x Mock\Document)
        $embedObject = $object->getMongoDocument();

        $this->assert
            ->object($embedObject)
            ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

        $this->assert
            ->string($embedObject->getMongoString())
            ->isEqualTo('embed mongo string');

        $this->assert
            ->integer($embedObject->getMongoNumber())
            ->isEqualTo(1337);
        
        // Check the first embedded collection level (10 x Mock\Document)
        $embedCollection = $object->getMongoCollection();

        $this->assert
            ->array($embedCollection)
            ->isNotEmpty()
            ->hasSize(10);
        
        foreach($embedCollection as $embedDocument) {
            $this->assert
                ->object($embedDocument)
                ->isInstanceOf('Boomgo\tests\units\Mock\Document');

            $this->assert
                ->string($embedDocument->getMongoString())
                ->isEqualTo('a DOCUMENT collection-embed stored string');

            $this->assert
                ->variable($embedDocument->getAttribute())
                ->isNull();

            $embedArray = $embedDocument->getMongoArray();
            $this->assert
                    ->array($embedArray)
                    ->hasSize(3)
                    ->isIdenticalTo($assocArray);

            $this->assert
                    ->array($embedArray['containing'])
                    ->hasSize(4)
                    ->isIdenticalTo($array);

            // check the second nested collection level (3 x Mock\EmbedDocument)
            $nestedEmbedCollection = $embedDocument->getMongoCollectionEmbed();
            $this->assert
                ->array($nestedEmbedCollection)
                ->isNotEmpty()
                ->hasSize(3);

            foreach($nestedEmbedCollection as $nestedEmbedDocument) {
                $this->assert
                    ->object($nestedEmbedDocument)
                    ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

                $this->assert
                    ->string($nestedEmbedDocument->getMongoString())
                    ->isEqualTo('a EMBED DOCUMENT collection-embed stored string');

                $this->assert
                    ->variable($nestedEmbedDocument->getAttribute())
                    ->isNull();
                
                $nestedAssocArray = $nestedEmbedDocument->getMongoArray();
                $this->assert
                    ->array($nestedAssocArray)
                    ->hasSize(3)
                    ->isIdenticalTo($assocArray);

                $this->assert
                    ->array($nestedAssocArray['containing'])
                    ->hasSize(4)
                    ->isIdenticalTo($array);
            }
        }

        // Finally check the embedded collection of EmbedDocument at the root document (3 x Mock\EmbedDocument)
        $embedCollectionEmbed = $object->getMongoCollectionEmbed();
        $this->assert
                ->array($embedCollectionEmbed)
                ->isNotEmpty()
                ->hasSize(3);

        foreach ($embedCollectionEmbed as $embedDocumentEmbed)
        {
            $this->assert
                    ->object($embedDocumentEmbed)
                    ->isInstanceOf('Boomgo\tests\units\Mock\EmbedDocument');

            $this->assert
                ->string($embedDocumentEmbed->getMongoString())
                ->isEqualTo('a EMBED DOCUMENT collection-embed stored string');

            $this->assert
                ->variable($embedDocumentEmbed->getAttribute())
                ->isNull();
            
            $embedAssocArray = $embedDocumentEmbed->getMongoArray();
            $this->assert
                ->array($embedAssocArray)
                ->hasSize(3)
                ->isIdenticalTo($assocArray);

            $this->assert
                ->array($embedAssocArray['containing'])
                ->hasSize(4)
                ->isIdenticalTo($array);
        }

        // Ultimate non-so-unit test to check consistency between hydrate & toArray 
        $reverse = $mapper->toArray($object);
        $this->assert
            ->array($reverse)
            ->isIdenticalTo($data);
    }
}