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
    private $rollbackQueue;
    private $uuidFactory;
    private $entryRegistry;

    public function __construct(
        StorageInterface $storage, 
        UuidFactory $uuidFactory = null,
        EntryRegistry $entryRegistry = null
    )
    {
        $this->storage = $storage;
        $this->uuidFactory = $uuidFactory ?: new UuidFactory();
        $this->entryRegistry = $entryRegistry ?: new EntryRegistry();
        $this->operationQueue = new \SplQueue();
        $this->rollbackQueue = new \SplQueue();
    }

    public function lookupByPath($path)
    {
        if ($this->entryRegistry->hasPath($path)) {
            return $this->entryRegistry->getByPath($path);
        }

        $entry = $this->storage->lookupByPath($path);
        $this->entryRegistry->register($entry);

        return $entry;
    }

    public function lookupByUuid($uuid)
    {
        if ($this->entryRegistry->hasUuid($uuid)) {
            return $this->entryRegistry->getByUuid($uuid);
        }

        $entry = $this->storage->lookupByUuid($uuid);
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
        // TODO: Only support real transactions ?
        try {
            while (false === $this->operationQueue->isEmpty()) {
                $operation = $this->operationQueue->dequeue();
                $operation->commit($this->storage, $this->entryRegistry);
                $this->rollbackQueue[] = $operation;
            }
        } catch (\Exception $e) {
            // TODO: Test rollback
            while (false === $this->rollbackQueue->isEmpty()) {
                $operation = $this->rollbackQueue->dequeue();
                $operation->rollback($this->storage, $this->entryRegistry);
            }

            throw new \RuntimeException(
                'Could not flush the path manager',
                null, $e
            );
        }
    }
}
