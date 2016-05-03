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
use DTL\DoctrineCR\Mapping\Mapper;
use DTL\DoctrineCR\Mapping\MetadataLoader;
use DTL\DoctrineCR\Mapping\Loader;
use DTL\DoctrineCR\Path\PathManager;

class CRSubscriber implements EventSubscriber
{
    private $pathManager;
    private $metadataFactory;
    private $entityManager;
    private $mapper;

    public function __construct(
        PathManager $pathManager, 
        MetadataFactory $metadataFactory,
        EntityManager $entityManager
    )
    {
        $this->pathManager = $pathManager;
        $this->metadataFactory = $metadataFactory;
        $this->entityManager = $entityManager;

        $this->loader = new Loader(
            $pathManager,
            $metadataFactory,
            $entityManager
        );
        $this->metadataLoader = new MetadataLoader(
            $this->metadataFactory
        );
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postLoad,
            // pre flush is always raised
            Events::preFlush, 
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $this->metadataLoader->loadMetadata($args->getClassMetadata());
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->loader->mapToObject($args->getObject());
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
                // TODO: Use a path registry instead of fetching from the DB every time.
                $parentEntry = $this->pathManager->lookupByUuid(
                    $crMetadata->getUuidValue($parent)
                );
                $parentPath = $parentEntry->getPath();
            }
        }

        // if there is no UUID, assume this is a new object
        if (null === $uuid) {
            $pathEntry = $this->pathManager->register(
                PathHelper::join([$parentPath, $name]),
                get_class($object)
            );

            $crMetadata->setPropertyValue($object, $crMetadata->getUuidProperty(), $pathEntry->getUuid());

            // hydrate the object
            // TODO: is this good?
            $this->postLoad($args);
        }
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        $this->pathManager->flush();
    }
}
