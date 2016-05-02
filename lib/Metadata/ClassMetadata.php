<?php

namespace DTL\DoctrineCR\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    private $managed = false;
    private $uuidProperty;
    private $nameProperty;
    private $parentProperty;

    public function getUuidProperty() 
    {
        return $this->uuidProperty;
    }
    
    public function setUuidProperty($uuidProperty)
    {
        $this->uuidProperty = $uuidProperty;
    }

    public function isManaged() 
    {
        return $this->managed;
    }
    
    public function setManaged($managed)
    {
        $this->managed = $managed;
    }

    public function getNameProperty() 
    {
        return $this->nameProperty;
    }
    
    public function setNameProperty($nameProperty)
    {
        $this->nameProperty = $nameProperty;
    }

    public function getParentProperty() 
    {
        return $this->parentProperty;
    }
    
    public function setParentProperty($parentProperty)
    {
        $this->parentProperty = $parentProperty;
    }
}
