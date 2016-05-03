<?php

namespace DTL\DoctrineCR\Path;

use DTL\DoctrineCR\Path\StorageInterface;
use Ramsey\Uuid\UuidFactory;
use DTL\DoctrineCR\Helper\PathHelper;
use DTL\DoctrineCR\Operation\Operation\CreateOperation;
use DTL\DoctrineCR\Operation\Operation\MoveOperation;
use DTL\DoctrineCR\Path\Entry;

class PathManager
{
    private $storage;
    private $operationQueue;
    private $rollbackQueue;
    private $uuidFactory;

    public function __construct(StorageInterface $storage, UuidFactory $uuidFactory = null)
    {
        $this->storage = $storage;
        $this->uuidFactory = $uuidFactory ?: new UuidFactory();
        $this->operationQueue = new \SplQueue();
        $this->rollbackQueue = new \SplQueue();
    }

    public function lookupByPath($path)
    {
        if ($this->registry->hasPath($path)) {
            return $this->registry->getByPath($path);
        }

        $entry = $this->storage->lookupByPath($path);
        $this->registry->register($entry);

        return $entry;
    }

    public function lookupByUuid($uuid)
    {
        return $this->storage->lookupByUuid($uuid);
    }

    public function getChildren($path)
    {
        return $this->storage->getChildren($path);
    }

    public function register($path, $classFqn)
    {
        $pathEntry = new Entry(
            $this->uuidFactory->uuid4(),
            $path,
            $classFqn,
            PathHelper::getDepth($path)
        );

        $this->operationQueue[] = new CreateOperation($pathEntry);

        return $pathEntry;
    }

    public function move($srcUuid, $destPAth)
    {
        $this->operationQueue[] = new MoveOperation($srcUuid, $destIdentifier);
    }

    public function flush()
    {
        try {
            while ($operation = $this->operationQueue->dequeue()) {
                $operation->commit($this->storage);
                $this->rollbackQueue[] = $operation;
            }
        } catch (\Exception $e) {
            while ($operation = $this->rollbackQueue->dequeue()) {
                $operation->rollback($this->storage);
            }

            throw new \RuntimeException(
                'Could not flush the path manager',
                null, $e
            );
        }
    }
}
