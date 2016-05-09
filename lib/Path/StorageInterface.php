<?php

namespace DoctrineCr\Path;

use DoctrineCr\Path\Entry;

interface StorageInterface
{
    public function getByPath($path);

    public function getByUuid($uuid);

    public function getChildren($path);

    public function commit(Entry $entry);

    public function remove($uuid);

    public function move($srcUuid, $destPath);

    public function startTransaction();

    public function commitTransaction();

    public function rollbackTransaction();
}
