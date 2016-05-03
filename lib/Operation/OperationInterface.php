<?php

namespace DTL\DoctrineCR\Operation;

use DTL\DoctrineCR\Path\StorageInterface;

interface OperationInterface
{
    public function commit(StorageInterface $storage);

    public function rollback(StorageInterface $storage);
}
