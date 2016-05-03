<?php

namespace DTL\DoctrineCR\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DTL\DoctrineCR\Path\StorageInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Metadata\MetadataFactory;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use DTL\DoctrineCR\Helper\PathHelper;
use DTL\DoctrineCR\Path\Exception\PathNotFoundException;
use DTL\DoctrineCR\Collection\ChildrenCollection;

class CRSubscriber implements EventSubscriber
{
    private $pathStorage;
    private $metadataFactory;
    private $entityManager;

    public function __construct(
        StorageInterface $pathStorage, 
        MetadataFactory $metadataFactory,
        EntityManager $entityManager
    )
    {
        $this->pathStorage = $pathStorage;
        $this->metadataFactory = $metadataFactory;
        $this->entityManager = $entityManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postLoad,
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $loaded = true;
        $dcMetadata = $args->getClassMetadata();
        $crMetadata = $this->metadataFactory->getMetadataForClass($dcMetadata->getName());

        $uuidProperty = $nameProperty = $parentProperty = null;
        $uuidProperty = $crMetadata->getUuidProperty();
        $nameProperty = $crMetadata->getNameProperty();
        $parentProperty = $crMetadata->getParentProperty();

        if (null === $uuidProperty) {
            throw new \RuntimeException(
                'No property has been mapped as a "UUID" field in class "%s"',
                $crMetadata->getName()
            );
        }

        if (null === $nameProperty) {
            throw new \RuntimeException(
                'No property has been mapped as a "name" field in class "%s"',
                $crMetadata->getName()
            );
        }

        if (false === $dcMetadata->hasField($uuidProperty)) {
            $dcMetadata->setIdentifier([$uuidProperty]);
            $dcMetadata->mapField([
                'fieldName' => $uuidProperty,
                'type' => 'string',
                'length' => 32
            ]);
        }

        if (false === $dcMetadata->hasField($nameProperty)) {
            $dcMetadata->mapField([
                'fieldName' => $nameProperty,
                'type' => 'string',
            ]);
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $crMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        // TODO: Only do this if we need it.
        $pathEntry = $this->pathStorage->lookupByUuid(
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
                $parentEntry = $this->pathStorage->lookupByPath($parentPath);
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
                $this->pathStorage,
                $pathEntry
            );
            $crMetadata->setPropertyValue(
                $object,
                $childrenMapping->getName(),
                $children
            );
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $crMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        $uuid = $crMetadata->getUuidValue($object);
        $name = $crMetadata->getPropertyValue($object, $crMetadata->getNameProperty());
        $parent = $crMetadata->getPropertyValue($object, $crMetadata->getParentProperty());

        $parentPath = '/';
        if ($parentProperty = $crMetadata->getParentProperty()) {
            $parent = $crMetadata->getPropertyValue($object, $crMetadata->getParentProperty());
            if ($parent) {
                // TODO: Handle proxy objects here
                // TODO: Use a path registry instead of fetching from the DB every time.
                $parentEntry = $this->pathStorage->lookupByUuid(
                    $crMetadata->getUuidValue($parent)
                );
                $parentPath = $parentEntry->getPath();
            }
        }

        if (null === $uuid) {
            $pathEntry = $this->pathStorage->register(
                PathHelper::join([$parentPath, $name]),
                get_class($object)
            );

            $crMetadata->setPropertyValue($object, $crMetadata->getUuidProperty(), $pathEntry->getUuid());
            $crMetadata->setPropertyValue($object, $crMetadata->getPathProperty(), $pathEntry->getPath());
        }
    }
}
