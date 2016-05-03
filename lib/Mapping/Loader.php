<?php

namespace DTL\DoctrineCR\Mapping;

use DTL\DoctrineCR\Path\StorageInterface;
use Metadata\MetadataFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use DTL\DoctrineCR\Helper\PathHelper;
use DTL\DoctrineCR\Collection\ChildrenCollection;
use DTL\DoctrineCR\Path\PathManager;

class Loader
{
    private $metadataFactory;
    private $pathManager;
    private $entityManager;

    public function __construct(
        PathManager $pathManager, 
        MetadataFactory $metadataFactory,
        EntityManager $entityManager
    )
    {
        $this->pathManager = $pathManager;
        $this->metadataFactory = $metadataFactory;
        $this->entityManager = $entityManager;
    }

    public function mapToObject($object)
    {
        $crMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        // TODO: Only do this if we need it.
        $pathEntry = $this->pathManager->lookupByUuid(
            $crMetadata->getUuidValue($object)
        );

        if ($pathProperty = $crMetadata->getPathProperty()) {
            $crMetadata->setPropertyValue(
                $object,
                $pathProperty,
                $pathEntry->getPath()
            );
        }

        if ($depthProperty = $crMetadata->getDepthProperty()) {
            $crMetadata->setPropertyValue(
                $object,
                $depthProperty,
                $pathEntry->getDepth()
            );
        }

        if ($parentProperty = $crMetadata->getParentProperty()) {
            $parentPath = PathHelper::getParentPath($pathEntry->getPath());

            // the parent path can be null if it is the root node
            if ($parentPath !== '/') {
                $parentEntry = $this->pathManager->lookupByPath($parentPath);
                $parentCrMetadata = $this->metadataFactory->getMetadataForClass($parentEntry->getClassFqn());
                $parent = $this->entityManager->getReference(
                    $parentEntry->getClassFqn(),
                    $parentEntry->getUuid()
                );

                $crMetadata->setPropertyValue(
                    $object,
                    $parentProperty,
                    $parent
                );
            }
        }

        foreach ($crMetadata->getChildrenMappings() as $childrenMapping) {

            $children = new ChildrenCollection(
                $this->entityManager,
                $this->metadataFactory,
                $this->pathManager,
                $pathEntry
            );
            $crMetadata->setPropertyValue(
                $object,
                $childrenMapping->getName(),
                $children
            );
        }
    }
}
