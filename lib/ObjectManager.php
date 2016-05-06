<?php

namespace DTL\DoctrineCR;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use DTL\DoctrineCR\Path\PathManagerInterface;
use DTL\DoctrineCR\Helper\UuidHelper;
use DTL\DoctrineCR\Path\PathManager;
use DTL\DoctrineCR\Events as CREvents;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DTL\DoctrineCR\Event\MoveEvent;


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
        $this->getEventManager()->dispatchEvent(CREvents::prePersist, new LifecycleEventArgs($object, $this));
        $this->wrapped->persist($object);
        $this->getEventManager()->dispatchEvent(CREvents::postPersist, new LifecycleEventArgs($object, $this));
    }

    public function move($srcIdentifier, $destPath)
    {
        $srcUuid = $srcIdentifier;

        if (false === UuidHelper::isUuid($srcIdentifier)) {
            $srcUuid = $this->pathManager->getByPath($srcIdentifier)->getUuid();
        }

        $this->pathManager->move($srcUuid, $destPath);
        $this->getEventManager()->dispatchEvent(CREvents::postMove, new MoveEvent($this, $srcIdentifier, $destPath));
    }
}
