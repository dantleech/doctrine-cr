<?php

namespace DoctrineCr\Operation\Operation;

use DoctrineCr\Operation\OperationInterface;
use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Path\EntryRegistry;

class RemoveOperation implements OperationInterface
{
    private $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function commit(StorageInterface $storage)
    {
        $storage->remove($this->uuid);
    }
}
