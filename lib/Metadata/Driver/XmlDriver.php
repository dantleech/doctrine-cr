<?php

namespace DTL\DoctrineCR\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use PhpBench\Dom\Document;
use DTL\DoctrineCR\Metadata\ClassMetadata;
use DTL\DoctrineCR\Metadata\PropertyMetadata;

class XmlDriver extends AbstractFileDriver implements DriverInterface
{
    const NAMESPACE_URI = 'http://github.com/dantleech/doctrine-cr';

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $classMetadata = new ClassMetadata($class->getName());

        $xml = new Document();
        $xml->load($file);
        $xml->xpath()->registerNamespace(
            'cr',
            self::NAMESPACE_URI
        );
        $xml->xpath()->registerNamespace(
            'doc',
            'http://doctrine-project.org/schemas/orm/doctrine-mapping'
        );

        if (0 == $xml->evaluate('count(./doc:entity/cr:managed)')) {
            $classMetadata->setManaged(false);
            return $classMetadata;
        }

        $classMetadata->setManaged(true);

        $entityEl = $xml->queryOne('./doc:entity');

        $classMetadata->setUuidProperty(
            $entityEl->evaluate('string(./cr:uuid/@name)')
        );
        $classMetadata->setNameProperty(
            $entityEl->evaluate('string(./cr:name/@name)')
        );
        $classMetadata->setParentProperty(
            $entityEl->evaluate('string(./cr:parent/@name)')
        );

        foreach ($entityEl->query('./cr:field') as $fieldEl) {
            $propertyMetadata = new PropertyMetadata($class->getName(), $fieldEl->getAttribute('name'));
            $propertyMetadata->setType($fieldEl->getAttribute('type'));
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }
}
