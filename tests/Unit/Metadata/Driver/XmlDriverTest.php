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
        $reflection = new \ReflectionClass(\stdClass::class);
        $this->locator->findFileForClass($reflection, 'xml')->willReturn(__DIR__ . '/xml/stdClass1.xml');

        $metadata = $this->driver->loadMetadataForClass($reflection);

        $this->assertTrue($metadata->isManaged());
        $this->assertEquals('uuid', $metadata->getUuidProperty());
        $this->assertEquals('title', $metadata->getNameProperty());
        $this->assertEquals('parent', $metadata->getParentProperty());

        $this->assertCount(1, $metadata->propertyMetadata);
        $property = reset($metadata->propertyMetadata);
        $this->assertEquals('children', $property->getType());
    }
}
