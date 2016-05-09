<?php

namespace DoctrineCr\Operation;

use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Path\EntryRegistry;

interface OperationInterface
{
    public function commit(StorageInterface $storage);
}
