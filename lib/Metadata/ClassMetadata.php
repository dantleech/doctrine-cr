<?php

namespace DTL\DoctrineCR\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    private $isNode;
    private $uuidPropertyName;

    public function getUuidPropertyName() 
    {
        return $this->uuidPropertyName;
    }
    
    public function setUuidPropertyName($uuidPropertyName)
    {
        $this->uuidPropertyName = $uuidPropertyName;
    }

    public function getIsNode() 
    {
        return $this->isNode;
    }
    
    public function setIsNode($isNode)
    {
        $this->isNode = $isNode;
    }
}
