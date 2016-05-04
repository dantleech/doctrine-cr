<?php

namespace DTL\DoctrineCR\Collection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Metadata\MetadataFactory;
use DTL\DoctrineCR\Path\Entry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DTL\DoctrineCR\Path\PathManager;

class ChildrenCollection extends AbstractLazyCollection
{
    private $entityManager;
    private $pathManager;
    private $pathEntry;
    private $metadataFactory;

    public function __construct(
        EntityManager $entityManager,
        MetadataFactory $metadataFactory,
        PathManager $pathManager,
        Entry $pathEntry
    )
    {
        $this->entityManager = $entityManager;
        $this->pathManager = $pathManager;
        $this->pathEntry = $pathEntry;
        $this->metadataFactory = $metadataFactory;
    }

    protected function doInitialize()
    {
        $childEntries = $this->pathManager->getChildren(
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
