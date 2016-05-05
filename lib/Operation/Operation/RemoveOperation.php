<?php

namespace DTL\DoctrineCR\Operation\Operation;

use DTL\DoctrineCR\Operation\OperationInterface;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\EntryRegistry;

class RemoveOperation implements OperationInterface
{
    private $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function commit(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->remove($this->uuid);
    }

    public function rollback(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        throw new \BadMethodCallException(
            'Rollback not supported for remove'
        );
    }
}
