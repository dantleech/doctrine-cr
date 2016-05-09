<?php

namespace DoctrineCr\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;
use PhpBench\Dom\Document;
use DoctrineCr\Metadata\ClassMetadata;
use DoctrineCr\Metadata\PropertyMetadata;
use DoctrineCr\Metadata\Mapping\ChildrenMapping;

class XmlDriver extends AbstractFileDriver implements DriverInterface
{
    const NAMESPACE_URI = 'http://github.com/dantleech/doctrine-cr';

    private $extension;

    public function __construct(FileLocatorInterface $locator, $extension = '.dcm.xml')
    {
        parent::__construct($locator);
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension;
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
        $classMetadata->setPathProperty(
            $entityEl->evaluate('string(./cr:path/@name)')
        );
        $classMetadata->setDepthProperty(
            $entityEl->evaluate('string(./cr:depth/@name)')
        );

        foreach ($entityEl->query('./cr:children') as $childrenEl) {
            $mapping = new ChildrenMapping();
            $mapping->setName($childrenEl->getAttribute('name'));
            $classMetadata->addChildrenMapping($mapping);
        }

        return $classMetadata;
    }
}
