<?php

namespace DoctrineCr\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;
use Metadata\MergeableInterface;
use DoctrineCr\Metadata\Mapping\ChildrenMapping;

class ClassMetadata extends BaseClassMetadata implements MergeableInterface
{
    private $managed = false;
    private $uuidProperty;
    private $nameProperty;
    private $parentProperty;
    private $pathProperty;
    private $childrenMappings = [];

    // TODO: Test me
    public function merge(MergeableInterface $metadata)
    {
        if ($metadata->getUuidProperty()) {
            $this->uuidProperty = $metadata->getUuidProperty();
        }

        if ($metadata->getNameProperty()) {
            $this->nameProperty = $metadata->getNameProperty();
        }

        if ($metadata->getParentProperty()) {
            $this->parentProperty = $metadata->getParentProperty();
        }

        if ($metadata->getPathProperty()) {
            $this->pathProperty = $metadata->getPathProperty();
        }
    }

    public function getUuidValue($object)
    {
        return $this->getPropertyValue($object, $this->getUuidProperty());
    }

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

    public function setPropertyValue($object, $propertyName, $value)
    {
        // TODO: Cache the reflection property
        $prop = $this->reflection->getProperty($propertyName);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function getPropertyValue($object, $propertyName)
    {
        $prop = $this->reflection->getProperty($propertyName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    public function getPathProperty() 
    {
        return $this->pathProperty;
    }
    
    public function setPathProperty($pathProperty)
    {
        $this->pathProperty = $pathProperty;
    }

    public function getDepthProperty() 
    {
        return $this->depthProperty;
    }
    
    public function setDepthProperty($depthProperty)
    {
        $this->depthProperty = $depthProperty;
    }

    public function addChildrenMapping(ChildrenMapping $mapping)
    {
        $this->childrenMappings[] = $mapping;
    }

    public function getChildrenMappings() 
    {
        return $this->childrenMappings;
    }
    
}
