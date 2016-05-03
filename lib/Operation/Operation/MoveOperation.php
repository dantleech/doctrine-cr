<?php

namespace DTL\DoctrineCR\Operation\Operation;

use DTL\DoctrineCR\Operation\OperationInterface;
use DTL\DoctrineCR\Path\StorageInterface;

class MoveOperation implements OperationInterface
{
    private $srcPath;
    private $destPath;

    public function __construct($srcPath, $destPath)
    {
        $this->srcPath = $srcPath;
        $this->destPath = $destPath;
    }

    public function commit(StorageInterface $storage)
    {
        $this->storage->move($this->srcPath, $this->destPath);
    }

    public function rollback(StorageInterface $storage)
    {
        $this->storage->move($this->destPath, $this->srcPath);
    }
}
