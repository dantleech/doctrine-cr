<?php

namespace DTL\DoctrineCR\Metadata\Driver;

use Metadata\Driver\DriverInterface;

class ArrayDriver implements DriverInterface
{
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return \Metadata\ClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
    }
}
