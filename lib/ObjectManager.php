<?php

namespace DTL\DoctrineCR;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use DTL\DoctrineCR\Path\StorageInterface;

class ObjectManager extends EntityManagerDecorator
{
    private $storage;

    public function __construct(StorageInterface $storage, EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function find($entityName, $id, $lockMode = null, $lockVersion = null)
    {
        $entry = $this->storage->lookUpUuid($id);

        return $this->wrapped->find(
            $entry->getTargetClassFqn(),
            $entry->getTargetId(),
            $lockMode,
            $lockVersion
        );
    }
}
