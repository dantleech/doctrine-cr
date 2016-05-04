<?php

namespace DTL\DoctrineCR\Path;

use DTL\DoctrineCR\Path\Exception\RegistryException;

class EntryRegistry
{
    private $entries = [];
    private $uuidsByPath = [];

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
    }

    // TODO: Test!
    public function move($srcPath, $destPath)
    {
        foreach ($this->entries as $uuid => $entry) {
            if ($entry->getPath() === $srcPath || 0 === strpos($entry->getPath(), $srcPath . '/')) {
                $newEntry = new Entry(
                    $entry->getUuid(),
                    $destPath . substr($entry->getPath(), strlen($srcPath)),
                    $entry->getClassFqn()
                );
                $this->remove($uuid);
                $this->register($newEntry);
            }
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
}
