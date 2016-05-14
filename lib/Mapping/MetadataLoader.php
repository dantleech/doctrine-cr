<?php

namespace DoctrineCr\Mapping;

use Metadata\MetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;

class MetadataLoader
{
    private $metadataFactory;

    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function loadMetadata(ClassMetadata $dcMetadata)
    {
        $crMetadata = $this->metadataFactory->getMetadataForClass($dcMetadata->getName());

        $uuidProperty = $nameProperty = $parentProperty = null;
        $uuidProperty = $crMetadata->getUuidProperty();
        $nameProperty = $crMetadata->getNameProperty();
        $parentProperty = $crMetadata->getParentProperty();

        if (null === $uuidProperty) {
            throw new \RuntimeException(sprintf(
                'No property has been mapped as a "UUID" field in class "%s"',
                $crMetadata->name
            ));
        }

        if (null === $nameProperty) {
            throw new \RuntimeException(
                'No property has been mapped as a "name" field in class "%s"',
                $crMetadata->getName()
            );
        }

        // create a field for the UUID and set it as the primary key.
        if (false === $dcMetadata->hasField($uuidProperty)) {
            $dcMetadata->setIdentifier([$uuidProperty]);
            $dcMetadata->mapField([
                'fieldName' => $uuidProperty,
                'type' => 'string',
                'length' => 36
            ]);
        }

        // create a field for the node name
        if (false === $dcMetadata->hasField($nameProperty)) {
            $dcMetadata->mapField([
                'fieldName' => $nameProperty,
                'type' => 'string',
            ]);
        }
    }
}
