<?php

namespace DTL\DoctrineCR\Tests\Unit\Metadata\Driver;

use Metadata\Driver\FileLocatorInterface;
use DTL\DoctrineCR\Metadata\Driver\XmlDriver;

class XmlDriverTest extends \PHPUnit_Framework_TestCase
{
    private $driver;
    private $locator;

    public function setUp()
    {
        $this->locator = $this->prophesize(FileLocatorInterface::class);
        $this->driver = new XmlDriver($this->locator->reveal());
    }

    /**
     * It should load the metadata for a class.
     */
    public function testLoadMetadata()
    {
        $reflection = new \ReflectionClass(TestEntity::class);
        $this->locator->findFileForClass($reflection, '.dcm.xml')->willReturn(__DIR__ . '/xml/valid1.xml');

        $metadata = $this->driver->loadMetadataForClass($reflection);

        $this->assertTrue($metadata->isManaged());
        $this->assertEquals('uuid', $metadata->getUuidProperty());
        $this->assertEquals('title', $metadata->getNameProperty());
        $this->assertEquals('parent', $metadata->getParentProperty());

        $this->assertCount(1, $mappings = $metadata->getChildrenMappings());
        $this->assertEquals('children', $mappings[0]->getName());
    }
}

class TestEntity
{
    private $children;
}
