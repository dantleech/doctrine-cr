<?php

namespace DTL\DoctrineCR\Path;

class Entry
{
    private $uuid;
    private $path;
    private $classFqn;
    private $depth;

    public function __construct(
        $uuid,
        $path,
        $classFqn
    )
    {
        $this->uuid = $uuid;
        $this->path = $path;
        $this->classFqn = $classFqn;
    }

    public function getUuid() 
    {
        return $this->uuid;
    }

    public function getPath() 
    {
        return $this->path;
    }

    public function getClassFqn() 
    {
        return $this->classFqn;
    }

    public function getDepth()
    {
        return PathHelper::getDepth($this->path);
    }
}
