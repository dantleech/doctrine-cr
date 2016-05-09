<?php

namespace DoctrineCr\Tests\Unit\Path;

use DoctrineCr\Path\PathManager;
use DoctrineCr\Path\StorageInterface;
use DoctrineCr\Operation\OperationInterface;

class PathManagerTest extends \PHPUnit_Framework_TestCase
{
    private $manager;
    private $storage;
    private $operationQueue;

    public function setUp()
    {
        $this->storage = $this->prophesize(StorageInterface::class);
        $this->operationQueue = new \SplQueue();
        $this->manager = new PathManager(
            $this->storage->reveal(),
            null,
            null,
            $this->operationQueue
        );
    }

    /**
     * It should rollback transactions.
     */
    public function testRollbackOnOperationException()
    {
        $operation = $this->prophesize(OperationInterface::class);
        $operation->commit($this->storage->reveal())->willThrow(new \RuntimeException('Foobar'));
        $this->operationQueue->enqueue($operation->reveal());

        try {
            $this->manager->flush();
            $this->fail('Operation did not throw exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Foobar', $e->getMessage());
        }

        $this->storage->startTransaction()->shouldHaveBeenCalled();
        $this->storage->commitTransaction()->shouldNotHaveBeenCalled();
        $this->storage->rollbackTransaction()->shouldHaveBeenCalled();
    }
}
