<?php

namespace DTL\DoctrineCR\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Events as CREvents;
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
use Doctrine\ORM\Event\PreFlushEventArgs;
use DTL\DoctrineCR\Mapping\Persister;
use DTL\DoctrineCR\Event\MoveEvent;

class CRSubscriber implements EventSubscriber
{
    private $pathManager;
    private $metadataFactory;
    private $entityManager;
    private $mapper;

    public function __construct(
        PathManager $pathManager, 
        MetadataFactory $metadataFactory,
        EntityManager $entityManager // TODO: This is available in the event, not required here.
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
        $this->persister = new Persister(
            $pathManager,
            $metadataFactory
        );
    }

    public function getSubscribedEvents()
    {
        return [
            CREvents::prePersist,
            CREvents::postPersist,
            CREvents::postMove,
            Events::preRemove,
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

    public function dcrPrePersist(LifecycleEventArgs $args)
    {
        $new = $this->persister->persist($args->getObject());
    }

    public function dcrPostPersist(LifecycleEventArgs $args)
    {
        $this->updateEntities();
    }

    public function dcrPostMove(MoveEvent $args)
    {
        $this->updateEntities();
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        $this->pathManager->flush();
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $metadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getRealClass(get_class($object)));

        if (!$metadata->isManaged()) {
            return;
        }

        $this->pathManager->remove($metadata->getUuidValue($object));
    }

    /**
     * Update any entities that have been affected by a path change.
     */
    private function updateEntities()
    {
        $updateQueue = $this->pathManager->getUpdateQueue();

        while (false === $updateQueue->isEmpty()) {
            $entry = $updateQueue->dequeue();
            $object = $this->entityManager->find($entry->getClassFqn(), $entry->getUuid());
            $this->loader->mapToObject($object);
        }
    }
}
