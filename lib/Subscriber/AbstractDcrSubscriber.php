<?php

namespace DoctrineCr\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Events as DcrEvents;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Metadata\MetadataFactory;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use DoctrineCr\Helper\PathHelper;
use DoctrineCr\Path\Exception\PathNotFoundException;
use DoctrineCr\Collection\ChildrenCollection;
use DoctrineCr\Mapping\Mapper;
use DoctrineCr\Mapping\MetadataLoader;
use DoctrineCr\Mapping\Loader;
use DoctrineCr\Path\PathManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use DoctrineCr\Mapping\Persister;
use DoctrineCr\Event\MoveEvent;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractDcrSubscriber implements EventSubscriber
{
    private $loader;
    private $metadataLoader;
    private $persister;
    private $metadataFactory;

    public function __construct(
        MetadataFactory $metadataFactory
    )
    {
        $this->metadataFactory = $metadataFactory;
    }

    protected function getLoader()
    {
        if ($this->loader) {
            return $this->loader;
        }

        $this->loader = new Loader(
            $this->getPathManager(),
            $this->metadataFactory
        );

        return $this->loader;
    }

    protected function getPersister()
    {
        if ($this->persister) {
            return $this->persister;
        }

        $this->persister = new Persister(
            $this->getPathManager(),
            $this->metadataFactory
        );

        return $this->persister;
    }

    protected function getMetadataLoader()
    {
        if ($this->metadataLoader) {
            return $this->metadataLoader;
        }

        $this->metadataLoader = new MetadataLoader(
            $this->metadataFactory
        );

        return $this->metadataLoader;
    }

    abstract protected function getPathManager();

    public function getSubscribedEvents()
    {
        return [
            DcrEvents::prePersist,
            DcrEvents::postPersist,
            DcrEvents::postMove,
            Events::preRemove,
            Events::postLoad,
            // pre flush is always raised
            Events::preFlush, 
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $this->getMetadataLoader()->loadMetadata($args->getClassMetadata());
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->getLoader()->mapToObject($args->getObjectManager(), $args->getObject());
    }

    public function dcrPrePersist(LifecycleEventArgs $args)
    {
        $this->getPersister()->persist($args->getObject());
    }

    public function dcrPostPersist(LifecycleEventArgs $args)
    {
        $this->updateEntities($args->getObjectManager());
    }

    public function dcrPostMove(MoveEvent $args)
    {
        $this->updateEntities($args->getObjectManager());
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        $this->getPathManager()->flush();
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $metadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        if (!$metadata->isManaged()) {
            return;
        }

        $this->getPathManager()->remove($metadata->getUuidValue($object));
    }

    /**
     * Update any entities that have been affected by a path change.
     */
    private function updateEntities(ObjectManager $objectManager)
    {
        $updateQueue = $this->getPathManager()->getUpdateQueue();

        while (false === $updateQueue->isEmpty()) {
            $entry = $updateQueue->dequeue();
            $object = $objectManager->find($entry->getClassFqn(), $entry->getUuid());
            $this->getLoader()->mapToObject($objectManager, $object);
        }
    }
}
