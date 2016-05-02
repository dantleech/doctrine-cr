<?php

namespace DTL\DoctrineCR\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    private $type;

    public function getType() 
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
}
