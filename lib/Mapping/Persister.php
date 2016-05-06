<?php

namespace DTL\DoctrineCR\Mapping;

use DTL\DoctrineCR\Path\PathManager;
use Metadata\MetadataFactory;
use DTL\DoctrineCR\Helper\PathHelper;
use Doctrine\Common\Util\ClassUtils;

class Persister
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

    public function persist($object)
    {
        $crMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        $uuid = $crMetadata->getUuidValue($object);
        $name = $crMetadata->getPropertyValue($object, $crMetadata->getNameProperty());
        $parent = $crMetadata->getPropertyValue($object, $crMetadata->getParentProperty());

        // determine path
        $parentPath = '/';
        if ($parentProperty = $crMetadata->getParentProperty()) {
            $parent = $crMetadata->getPropertyValue($object, $crMetadata->getParentProperty());

            if ($parent) {
                $parentEntry = $this->pathManager->getByUuid(
                    $crMetadata->getUuidValue($parent)
                );
                $parentPath = $parentEntry->getPath();
            }
        }

        $path = PathHelper::join([$parentPath, $name]);

        // if there is no UUID, assume this is a new object
        if (null === $uuid) {
            $pathEntry = $this->pathManager->createEntry(
                $path,
                get_class($object)
            );

            $crMetadata->setPropertyValue($object, $crMetadata->getUuidProperty(), $pathEntry->getUuid());
            return;
        }

        $pathEntry = $this->pathManager->getByUuid($uuid);

        if ($path !== $pathEntry->getPath()) {
            $this->pathManager->move($uuid, $path);
        }
    }
}
