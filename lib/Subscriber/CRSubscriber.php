<?php

namespace DTL\DoctrineCR\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DTL\DoctrineCR\Path\StorageInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Metadata\MetadataFactory;
use Doctrine\Common\Util\ClassUtils;

class CRSubscriber implements EventSubscriber
{
    private $pathStorage;
    private $metadataFactory;

    public function __construct(StorageInterface $pathStorage, MetadataFactory $metadataFactory)
    {
        $this->pathStorage = $pathStorage;
        $this->metadataFactory = $metadataFactory;
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

        if (!$dcMetadata->hasField($uuidProperty)) {
            $dcMetadata->setIdentifier([$uuidProperty]);
            $dcMetadata->mapField([
                'fieldName' => $uuidProperty,
                'type' => 'string',
                'length' => 32
            ]);
        }

        if (!$dcMetadata->hasField($nameProperty)) {
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

        if ($pathProperty = $crMetadata->getPathProperty()) {
            $pathEntry = $this->pathStorage->lookUpPath(
                $crMetadata->getUuidValue($object)
            );
            $crMetadata->setPropertyValue(
                $object,
                $pathProperty,
                $pathEntry->getPath()
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

        if (null === $uuid) {
            // TODO: use parent path
            $pathEntry = $this->pathStorage->register(
                '/' . $name,
                get_class($object)
            );

            $crMetadata->setPropertyValue($object, $crMetadata->getUuidProperty(), $pathEntry->getUuid());
            $crMetadata->setPropertyValue($object, $crMetadata->getPathProperty(), $pathEntry->getPath());
        }
    }
}
