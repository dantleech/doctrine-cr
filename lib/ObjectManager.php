<?php

namespace DoctrineCr;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineCr\Path\PathManagerInterface;
use DoctrineCr\Helper\UuidHelper;
use DoctrineCr\Path\PathManager;
use DoctrineCr\Events as DcrEvents;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DoctrineCr\Event\MoveEvent;


class ObjectManager extends EntityManagerDecorator
{
    private $pathManager;

    public function __construct(PathManager $pathManager, EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->pathManager = $pathManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find($entityName, $identifier, $lockMode = null, $lockVersion = null)
    {
        if (UuidHelper::isUuid($identifier)) {
            $entry = $this->pathManager->getByUuid($identifier);
        } else {
            $entry = $this->pathManager->getByPath($identifier);
        }

        return $this->wrapped->find(
            $entry->getClassFqn(),
            $entry->getUuid(),
            $lockMode,
            $lockVersion
        );
    }

    public function persist($object)
    {
        $this->getEventManager()->dispatchEvent(DcrEvents::prePersist, new LifecycleEventArgs($object, $this));
        $this->wrapped->persist($object);
        $this->getEventManager()->dispatchEvent(DcrEvents::postPersist, new LifecycleEventArgs($object, $this));
    }

    public function move($srcIdentifier, $destPath)
    {
        $srcUuid = $srcIdentifier;

        if (false === UuidHelper::isUuid($srcIdentifier)) {
            $srcUuid = $this->pathManager->getByPath($srcIdentifier)->getUuid();
        }

        $this->pathManager->move($srcUuid, $destPath);
        $this->getEventManager()->dispatchEvent(DcrEvents::postMove, new MoveEvent($this, $srcIdentifier, $destPath));
    }
}
