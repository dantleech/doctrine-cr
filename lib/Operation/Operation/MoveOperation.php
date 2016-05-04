<?php

namespace DTL\DoctrineCR\Operation\Operation;

use DTL\DoctrineCR\Operation\OperationInterface;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\EntryRegistry;

class MoveOperation implements OperationInterface
{
    private $srcPath;
    private $destPath;

    public function __construct($srcPath, $destPath)
    {
        $this->srcPath = $srcPath;
        $this->destPath = $destPath;
    }

    public function commit(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->move($this->srcPath, $this->destPath);
        $entryRegistry->move($this->srcPath, $this->destPath);
    }

    public function rollback(StorageInterface $storage, EntryRegistry $entryRegistry)
    {
        $storage->move($this->destPath, $this->srcPath);
    }
}
