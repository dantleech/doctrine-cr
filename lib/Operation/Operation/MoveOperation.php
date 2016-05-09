<?php

namespace DoctrineCr\Operation\Operation;

use DoctrineCr\Operation\OperationInterface;
use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Path\EntryRegistry;

class MoveOperation implements OperationInterface
{
    private $srcUuid;
    private $destPath;

    public function __construct($srcUuid, $destPath)
    {
        $this->srcUuid = $srcUuid;
        $this->destPath = $destPath;
    }

    public function commit(StorageInterface $storage)
    {
        $storage->move($this->srcUuid, $this->destPath);
    }
}
