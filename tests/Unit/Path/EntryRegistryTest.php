<?php

namespace DoctrineCr\Tests\Unit\Path;

use DoctrineCr\Path\EntryRegistry;
use DoctrineCr\Path\Entry;

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

        $this->assertSame($pathEntry, $this->registry->getByUuid('1234'));
        $this->assertSame($pathEntry, $this->registry->getByPath('/path/to'));
    }

    /**
     * It should throw an exception if a non-existing UUID is given.
     *
     * @expectedException \DoctrineCr\Path\Exception\RegistryException
     * @expectedExceptionMessage UUID "1234" is not registered, there are 0 registered entries.
     */
    public function testNonExistingUuid()
    {
        $this->registry->getByUuid('1234');
    }


    /**
     * It should throw an exception if a non-existing PATH is given.
     *
     * @expectedException \DoctrineCr\Path\Exception\RegistryException
     * @expectedExceptionMessage Path "1234" is not registered, there are 0 registered entries.
     */
    public function testNonExistingPath()
    {
        $this->registry->getByPath('1234');
    }

    /**
     * It should throw an exception if a path is already registered..
     *
     * @expectedException \DoctrineCr\Path\Exception\RegistryException
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
     * @expectedException \DoctrineCr\Path\Exception\RegistryException
     * @expectedExceptionMessage Entry for UUID "1234" has already been registered to object at path "/path/to/1" (ClassFqn)
     */
    public function testRegisterAlreadyExistingUuid()
    {
        $pathEntry = new Entry('1234', '/path/to/1', 'ClassFqn');
        $this->registry->register($pathEntry);
        $pathEntry = new Entry('1234', '/path/to/2', 'ClassFqn');
        $this->registry->register($pathEntry);
    }

    /**
     * It should move paths.
     *
     * @dataProvider provideMove
     */
    public function testMove(array $paths, $from, $to, array $expectedPaths)
    {
        $this->initMove($paths, $from, $to);

        // check the paths updated
        $this->assertEquals($expectedPaths, $this->registry->getPaths());

        // check the entries updated
        $paths = [];

        foreach ($this->registry->getEntries() as $entry) {
            $paths[] = $entry->getPath();
        }

        $this->assertEquals($expectedPaths, $paths);
    }

    public function provideMove()
    {
        return [
            [
                [
                    '/one/1/2/3',
                    '/one/1/2',
                    '/two',
                ],
                '/two',
                '/one/1/2/3/two',
                [
                    '/one/1/2/3',
                    '/one/1/2',
                    '/one/1/2/3/two',
                ]
            ],
            [
                [
                    '/one',
                    '/one/1/2/3',
                    '/one/1/2',
                    '/two',
                ],
                '/one',
                '/two/one',
                [
                    '/two',
                    '/two/one',
                    '/two/one/1/2/3',
                    '/two/one/1/2',
                ]
            ],
            [
                [
                    '/one',
                ],
                '/one',
                '/two',
                [
                    '/two',
                ]
            ],
        ];
    }

    /**
     * It should throw exceptions on invalid move operations.
     *
     * @dataProvider provideInvalidMove
     */
    public function testInvalidMove(array $paths, $from, $to, $expectedMessage)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $expectedMessage);
        $this->initMove($paths, $from, $to);
    }

    public function provideInvalidMove()
    {
        return [
            [
                [
                    '/two',
                ],
                '/two',
                '/two/two',
                'Error moving entry from "/two" to "/two/two", cannot move a node onto itself or one of its descendants.'
            ],
        ];
    }

    public function testRemove()
    {
        $this->initPaths(
            [
                '/path',
                '/path/to',
                '/path/to/me',
                '/foo',
            ]
        );

        $this->registry->remove(0);

        $this->assertEquals([
            '/foo'
        ], $this->registry->getPaths());
    }

    private function initMove(array $paths, $from, $to)
    {
        $this->initPaths($paths);
        $uuidsByPath = array_flip($paths);

        $this->registry->move($uuidsByPath[$from], $to);
    }

    private function initPaths(array $paths)
    {
        foreach ($paths as $index => $path) {
            $entry = new Entry((string) $index, $path, 'Cfqn');
            $this->registry->register($entry);
        }
    }
}
