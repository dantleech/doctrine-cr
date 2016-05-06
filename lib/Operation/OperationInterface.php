<?php

namespace DTL\DoctrineCR\Operation;

use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\EntryRegistry;

interface OperationInterface
{
    public function commit(StorageInterface $storage);
}
