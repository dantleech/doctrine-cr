<?php

namespace DTL\DoctrineCR\Path;

interface StorageInterface
{
    public function lookUpPath($path);

    public function lookUpUuid($uuid);

    public function store($path, $targetClassFqn);
}
