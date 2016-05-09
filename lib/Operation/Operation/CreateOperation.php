<?php

namespace DoctrineCr\Operation\Operation;

use DoctrineCr\Path\Entry;
use DoctrineCr\Operation\OperationInterface;
use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Path\EntryRegistry;

class CreateOperation implements OperationInterface
{
    private $entry;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function commit(StorageInterface $storage)
    {
        $storage->commit($this->entry);
    }
}
