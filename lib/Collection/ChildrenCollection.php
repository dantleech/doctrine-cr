<?php

namespace DoctrineCr\Collection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Metadata\MetadataFactory;
use DoctrineCr\Path\Entry;
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineCr\Path\PathManager;
use Doctrine\Common\Persistence\ObjectManager;

class ChildrenCollection extends AbstractLazyCollection
{
    private $objectManager;
    private $pathManager;
    private $pathEntry;
    private $metadataFactory;

    public function __construct(
        ObjectManager $objectManager,
        MetadataFactory $metadataFactory,
        PathManager $pathManager,
        Entry $pathEntry
    )
    {
        $this->objectManager = $objectManager;
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
            $children[] = $this->objectManager->getReference(
                $childEntry->getClassFqn(),
                $childEntry->getUuid()
            );
        }

        $this->collection = new ArrayCollection($children);
    }
}
