<?php

namespace DTL\DoctrineCR\Path;

use DTL\DoctrineCR\Path\Entry;

interface StorageInterface
{
    public function lookupByPath($path);

    public function lookupByUuid($uuid);

    public function getChildren($path);

    public function commit(Entry $entry);

    public function remove($uuid);

    public function move($srcUuid, $destPath);
}
