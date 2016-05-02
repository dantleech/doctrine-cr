<?php

namespace DTL\DoctrineCR\Tests\Functional\Resources\Entity;

class Page
{
    private $uuid;
    private $title;
    private $children;
    private $parent;
    private $path;
    private $contents;

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
    
}
