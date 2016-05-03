<?php

namespace DTL\DoctrineCR\Collection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Metadata\MetadataFactory;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\Entry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ChildrenCollection extends AbstractLazyCollection
{
    private $entityManager;
    private $pathStorage;
    private $pathEntry;
    private $metadataFactory;

    public function __construct(
        EntityManager $entityManager,
        MetadataFactory $metadataFactory,
        StorageInterface $pathStorage,
        Entry $pathEntry
    )
    {
        $this->entityManager = $entityManager;
        $this->pathStorage = $pathStorage;
        $this->pathEntry = $pathEntry;
        $this->metadataFactory = $metadataFactory;
    }

    protected function doInitialize()
    {
        $childEntries = $this->pathStorage->getChildren(
            $this->pathEntry->getUuid()
        );

        $children = [];
        foreach ($childEntries as $childEntry) {
            $childMetadata = $this->metadataFactory->getMetadataForClass($childEntry->getClassFqn());

            $children[] = $this->entityManager->getReference(
                $childEntry->getClassFqn(),
                $childEntry->getUuid()
            );
        }

        $this->collection = new ArrayCollection($children);
    }
}
