<?php

namespace DTL\DoctrineCR\Metadata;

abstract class AbstractMapping
{
    private $name;

    public function getName() 
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
}
