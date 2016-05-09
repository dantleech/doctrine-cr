<?php

namespace DoctrineCr\Mapping;

use DoctrineCr\Path\StorageInterface;
use Metadata\MetadataFactory;
use Doctrine\Common\Util\ClassUtils;
use DoctrineCr\Helper\PathHelper;
use DoctrineCr\Collection\ChildrenCollection;
use DoctrineCr\Path\PathManager;
use Doctrine\Common\Persistence\ObjectManager;

class Loader
{
    private $metadataFactory;
    private $pathManager;

    public function __construct(
        PathManager $pathManager, 
        MetadataFactory $metadataFactory
    )
    {
        $this->pathManager = $pathManager;
        $this->metadataFactory = $metadataFactory;
    }

    public function mapToObject(ObjectManager $objectManager, $object)
    {
        $crMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        // TODO: Only do this if we need it.
        $pathEntry = $this->pathManager->getByUuid(
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
                $parentEntry = $this->pathManager->getByPath($parentPath);
                $parentCrMetadata = $this->metadataFactory->getMetadataForClass($parentEntry->getClassFqn());
                $parent = $objectManager->getReference(
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
                $objectManager,
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
