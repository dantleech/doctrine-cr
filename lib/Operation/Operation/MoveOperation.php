<?php

namespace DTL\DoctrineCR\Operation\Operation;

use DTL\DoctrineCR\Operation\OperationInterface;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\EntryRegistry;

class MoveOperation implements OperationInterface
{
    private $srcUuid;
    private $destPath;

    public function __construct($srcUuid, $destPath)
    {
        $this->srcUuid = $srcUuid;
        $this->destPath = $destPath;
    }

    public function commit(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->move($this->srcUuid, $this->destPath);
    }

    public function rollback(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->move($this->destPath, $this->srcUuid);
    }
}
