<?php

namespace DTL\DoctrineCR\Path;

interface StorageInterface
{
    public function lookUpPath($path);

    public function lookUpUuid($uuid);

    public function register($path, $targetClassFqn);
}
