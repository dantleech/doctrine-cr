<?php

namespace DTL\DoctrineCR;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use DTL\DoctrineCR\Path\PathManagerInterface;
use DTL\DoctrineCR\Helper\UuidHelper;
use DTL\DoctrineCR\Path\PathManager;
use DTL\DoctrineCR\Events as CREvents;
use Doctrine\ORM\Event\LifecycleEventArgs;


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
            $entry = $this->pathManager->lookupByUuid($identifier);
        } else {
            $entry = $this->pathManager->lookupByPath($identifier);
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
        parent::persist($object);
    }

    public function move($srcIdentifier, $destPath)
    {
        $srcPath = $srcIdentifier;

        if (UuidHelper::isUuid($srcIdentifier)) {
            $srcPath = $this->pathManager->lookupByUuid($srcIdentifier)->getPath();
        }

        $this->pathManager->move($srcPath, $destPath);
    }
}
