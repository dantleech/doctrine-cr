<?php

namespace DoctrineCr\Tests\Functional\Resources\Entity;

class Page
{
    private $uuid;
    private $title;
    private $children;
    private $parent;
    private $path;
    private $contents;
    private $depth;

    public function getUuid() 
    {
        return $this->uuid;
    }
    

    public function getTitle() 
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getChildren() 
    {
        return $this->children;
    }

    public function getPath() 
    {
        return $this->path;
    }

    public function getParent() 
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getDepth() 
    {
        return $this->depth;
    }
}
