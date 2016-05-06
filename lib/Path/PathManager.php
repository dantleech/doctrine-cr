<?php

namespace DTL\DoctrineCR\Path;

use DTL\DoctrineCR\Path\StorageInterface;
use Ramsey\Uuid\UuidFactory;
use DTL\DoctrineCR\Helper\PathHelper;
use DTL\DoctrineCR\Operation\Operation\CreateOperation;
use DTL\DoctrineCR\Operation\Operation\MoveOperation;
use DTL\DoctrineCR\Path\Entry;
use DTL\DoctrineCR\Path\EntryRegistry;
use DTL\DoctrineCR\Operation\Operation\RemoveOperation;

class PathManager
{
    private $storage;
    private $operationQueue;
    private $uuidFactory;
    private $entryRegistry;

    public function __construct(
        StorageInterface $storage, 
        UuidFactory $uuidFactory = null,
        EntryRegistry $entryRegistry = null,
        \SplQueue $operationQueue = null
    )
    {
        $this->storage = $storage;
        $this->uuidFactory = $uuidFactory ?: new UuidFactory();
        $this->entryRegistry = $entryRegistry ?: new EntryRegistry();
        $this->operationQueue = $operationQueue ?: new \SplQueue();
    }

    public function getByPath($path)
    {
        if ($this->entryRegistry->hasPath($path)) {
            return $this->entryRegistry->getByPath($path);
        }

        $entry = $this->storage->getByPath($path);
        $this->entryRegistry->register($entry);

        return $entry;
    }

    public function getByUuid($uuid)
    {
        if ($this->entryRegistry->hasUuid($uuid)) {
            return $this->entryRegistry->getByUuid($uuid);
        }

        $entry = $this->storage->getByUuid($uuid);
        $this->entryRegistry->register($entry);

        return $entry;
    }

    public function getChildren($path)
    {
        $entries = $this->storage->getChildren($path);

        return $entries;
    }

    public function createEntry($path, $classFqn)
    {
        $pathEntry = new Entry(
            (string) $this->uuidFactory->uuid4(),
            $path,
            $classFqn,
            PathHelper::getDepth($path)
        );
        $this->entryRegistry->register($pathEntry);

        $this->operationQueue->push(new CreateOperation($pathEntry));

        return $pathEntry;
    }

    public function move($srcUuid, $destPath)
    {
        $this->operationQueue->push(new MoveOperation($srcUuid, $destPath));
        $this->entryRegistry->move($srcUuid, $destPath);
    }

    public function remove($uuid)
    {
        $this->operationQueue->push(new RemoveOperation($uuid));
        $this->entryRegistry->remove($uuid);
    }

    public function getRegisteredEntries()
    {
        return $this->entryRegistry->getEntries();
    }

    public function getRegisteredPaths()
    {
        return $this->entryRegistry->getPaths();
    }

    public function getUpdateQueue()
    {
        return $this->entryRegistry->getUpdateQueue();
    }

    public function flush()
    {
        $this->storage->startTransaction();

        $executedOperations = new \SplQueue();
        try {
            while (false === $this->operationQueue->isEmpty()) {
                $operation = $this->operationQueue->dequeue();
                $operation->commit($this->storage);
                $executedOperations->enqueue($operation);
            }

            $this->storage->commitTransaction();
        } catch (\Exception $e) {
            $this->storage->rollbackTransaction();

            // add the executed operations back to the operation queue..
            foreach ($executedOperations as $executedOperation) {
                $this->operationQueue->enqueue($executedOperation);
            }

            throw $e;
        }
    }
}
