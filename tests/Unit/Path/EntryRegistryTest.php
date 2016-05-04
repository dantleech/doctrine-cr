<?php

namespace DTL\DoctrineCR\Tests\Unit\Path;

use DTL\DoctrineCR\Path\EntryRegistry;
use DTL\DoctrineCR\Path\Entry;

class EntryRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;

    public function setUp()
    {
        $this->registry = new EntryRegistry();
    }

    /**
     * It should register path entries.
     */
    public function testRegister()
    {
        $pathEntry = new Entry('1234', '/path/to', 'ClassFqn');
        $this->registry->register($pathEntry);
        $this->assertTrue($this->registry->hasUuid('1234'));
        $this->assertTrue($this->registry->hasPath('/path/to'));
    }

    /**
     * It should return false if the path or uuid not has.
     */
    public function testRegisterNotHas()
    {
        $this->assertFalse($this->registry->hasUuid('1234'));
        $this->assertFalse($this->registry->hasPath('/path/to'));
    }

    /**
     * It should get by UUID or path.
     */
    public function testGetByUuidOrPath()
    {
        $pathEntry = new Entry('1234', '/path/to', 'ClassFqn');
        $this->registry->register($pathEntry);

        $this->assertSame($pathEntry, $this->registry->getForUuid('1234'));
        $this->assertSame($pathEntry, $this->registry->getForPath('/path/to'));
    }

    /**
     * It should throw an exception if a non-existing UUID is given.
     *
     * @expectedException \DTL\DoctrineCR\Path\Exception\RegistryException
     * @expectedExceptionMessage UUID "1234" is not registered, there are 0 registered entries.
     */
    public function testNonExistingUuid()
    {
        $this->registry->getForUuid('1234');
    }


    /**
     * It should throw an exception if a non-existing PATH is given.
     *
     * @expectedException \DTL\DoctrineCR\Path\Exception\RegistryException
     * @expectedExceptionMessage Path "1234" is not registered, there are 0 registered entries.
     */
    public function testNonExistingPath()
    {
        $this->registry->getForPath('1234');
    }

    /**
     * It should throw an exception if a path is already registered..
     *
     * @expectedException \DTL\DoctrineCR\Path\Exception\RegistryException
     * @expectedExceptionMessage Entry for path "/path/to" has already been registered to object with UUID "4321" (ClassFqn)
     */
    public function testRegisterAlreadyExistingPath()
    {
        $pathEntry = new Entry('4321', '/path/to', 'ClassFqn');
        $this->registry->register($pathEntry);
        $pathEntry = new Entry('1234', '/path/to', 'ClassFqn');
        $this->registry->register($pathEntry);
    }

    /**
     * It should throw an exception if a UUID is already registered..
     *
     * @expectedException \DTL\DoctrineCR\Path\Exception\RegistryException
     * @expectedExceptionMessage Entry for UUID "1234" has already been registered to object at path "/path/to/1" (ClassFqn)
     */
    public function testRegisterAlreadyExistingUuid()
    {
        $pathEntry = new Entry('1234', '/path/to/1', 'ClassFqn');
        $this->registry->register($pathEntry);
        $pathEntry = new Entry('1234', '/path/to/2', 'ClassFqn');
        $this->registry->register($pathEntry);
    }
}
