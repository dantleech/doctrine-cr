<?php

namespace DTL\DoctrineCR\Metadata\Locator;

use Metadata\Driver\FileLocatorInterface;
use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;

class DoctrineLocator implements FileLocatorInterface
{
    private $locator;

    public function __construct(FileLocator $locator)
    {
        $this->locator = $locator;
    }

    public function findFileForClass(\ReflectionClass $class, $extension)
    {
        return $this->locator->findMappingFile($class->getName());
    }
}
