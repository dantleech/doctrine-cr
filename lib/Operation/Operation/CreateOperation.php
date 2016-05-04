<?php

namespace DTL\DoctrineCR\Operation\Operation;

use DTL\DoctrineCR\Path\Entry;
use DTL\DoctrineCR\Operation\OperationInterface;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\EntryRegistry;

class CreateOperation implements OperationInterface
{
    private $entry;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function commit(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->commit($this->entry);
    }

    public function rollback(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->remove($this->entry->getUuid());
    }
}
