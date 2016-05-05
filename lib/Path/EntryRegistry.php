<?php

namespace DTL\DoctrineCR\Path;

use DTL\DoctrineCR\Path\Exception\RegistryException;
use DTL\DoctrineCR\Helper\PathHelper;

class EntryRegistry
{
    private $entries = [];
    private $uuidsByPath = [];
    private $updateQueue;

    public function __construct()
    {
        $this->updateQueue = new \SplQueue();
    }

    public function register(Entry $entry)
    {
        if (isset($this->entries[$entry->getUuid()])) {
            $existing = $this->entries[$entry->getUuid()];
            throw new RegistryException(sprintf(
                'Entry for UUID "%s" has already been registered to object at path "%s" (%s)',
                $entry->getUuid(),
                $existing->getPath(),
                $existing->getClassFqn()
            ));
        }

        if (isset($this->uuidsByPath[$entry->getPath()])) {
            $existing = $this->entries[$this->uuidsByPath[$entry->getPath()]];

            throw new RegistryException(sprintf(
                'Entry for path "%s" has already been registered to object with UUID "%s" (%s)',
                $entry->getPath(),
                $existing->getUuid(),
                $existing->getClassFqn()
            ));
        }

        $this->entries[$entry->getUuid()] = $entry;
        $this->uuidsByPath[$entry->getPath()] = $entry->getUuid();
        $this->updateQueue->enqueue($entry);
    }

    public function move($srcUuid, $destPath)
    {
        $srcEntry = $this->getForUuid($srcUuid);

        if (PathHelper::isSelfOrDescendant($srcEntry->getPath(), $destPath)) {
            throw new \InvalidArgumentException(sprintf(
                'Error moving entry from "%s" to "%s", cannot move a node onto itself or one of its descendants.',
                $srcEntry->getPath(), $destPath
            ));
        }

        $srcPath = $srcEntry->getPath();

        foreach ($this->entries as $uuid => $entry) {
            if (false === PathHelper::isSelfOrDescendant($srcPath, $entry->getPath())) {
               continue;
            }

            $newEntry = new Entry(
                $entry->getUuid(),
                $destPath . substr($entry->getPath(), strlen($srcPath)),
                $entry->getClassFqn()
            );

            // allow update entries to be dequeued (i.e. to load the new properties
            // onto the related entity).
            $this->updateQueue->enqueue($newEntry);
            $this->remove($newEntry->getUuid());
            $this->register($newEntry);
        }
    }

    public function hasPath($path)
    {
        return isset($this->uuidsByPath[$path]);
    }

    public function getForPath($path)
    {
        if (!$this->hasPath($path)) {
            throw new RegistryException(sprintf(
                'Path "%s" is not registered, there are %d registered entries.',
                $path, count($this->entries)
            ));
        }

        return $this->entries[$this->uuidsByPath[$path]];
    }

    public function hasUuid($uuid)
    {
        return isset($this->entries[$uuid]);
    }

    public function getForUuid($uuid)
    {
        if (!$this->hasUuid($uuid)) {
            throw new RegistryException(sprintf(
                'UUID "%s" is not registered, there are %d registered entries.',
                $uuid, count($this->entries)
            ));
        }

        return $this->entries[$uuid];
    }

    public function remove($uuid)
    {
        $entry = $this->getForUuid($uuid);
        unset($this->uuidsByPath[$entry->getPath()]);
        unset($this->entries[$uuid]);
    }

    public function getPaths()
    {
        return array_keys($this->uuidsByPath);
    }

    public function getEntries()
    {
        return $this->entries;
    }

    public function getUpdateQueue()
    {
        return $this->updateQueue;
    }
}
