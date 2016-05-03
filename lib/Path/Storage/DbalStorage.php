<?php

namespace DTL\DoctrineCR\Path\Storage;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidFactory;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Path\Storage\Dbal\Schema;
use DTL\DoctrineCR\Path\Entry;
use DTL\DoctrineCR\Path\Exception\PathNotFoundException;
use DTL\DoctrineCR\Path\Exception\PathAlreadyRegisteredException;
use DTL\DoctrineCR\Path\Exception\UuidNotFoundException;
use DTL\DoctrineCR\Helper\PathHelper;

class DbalStorage implements StorageInterface
{
    private $connection;
    private $uuidFactory;

    public function __construct(Connection $connection, UuidFactory $uuidFactory = null)
    {
        $this->connection = $connection;
        $this->uuidFactory = $uuidFactory ?: new UuidFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function lookupByPath($path)
    {
        $entry = $this->lookup('path', $path);

        if (null === $entry) {
            throw new PathNotFoundException($path);
        }

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function lookupByUuid($uuid)
    {
        $entry = $this->lookup('uuid', $uuid);

        if (null === $entry) {
            throw new UuidNotFoundException($uuid);
        }

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($uuid)
    {
        $entry = $this->lookupByUuid($uuid);
        $path = $entry->getPath();
        $pathDepth = PathHelper::getDepth($path);

        $sql = sprintf(
            'SELECT uuid, path, class_fqn FROM %s WHERE path LIKE ? AND depth = ?',
            Schema::TABLE_NAME
        );
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            $path . '%',
            $pathDepth + 1
        ]);
        $childRows = $stmt->fetchAll();

        $childEntries = [];
        foreach ($childRows as $childRow) {
            $childEntries[] = $this->rowToEntry($childRow);
        }

        return $childEntries;
    }

    /**
     * {@inheritdoc}
     */
    public function register($path, $classFqn)
    {
        // TODO: Handle existing paths in a performant way..?
        try {
            $pathEntry = $this->lookupByPath($path);
            throw new PathAlreadyRegisteredException(
                $path,
                $pathEntry->getUuid(),
                $pathEntry->getClassFqn()
            );
        } catch (PathNotFoundException $e) {
        }

        // TODO: Validate path
        $sql = sprintf(
            'INSERT INTO %s (uuid, path, class_fqn, depth) VALUES (?, ?, ?, ?)',
            Schema::TABLE_NAME
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            $uuid = (string) $this->uuidFactory->uuid4(),
            $path,
            $classFqn,
            PathHelper::getDepth($path)
        ]);

        return new Entry($uuid, $path, $classFqn);
    }

    private function lookup($columnName, $identifier)
    {
        $sql = sprintf(
            'SELECT uuid, path, class_fqn FROM %s WHERE %s = ?',
            Schema::TABLE_NAME,
            $columnName
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([ $identifier ]);
        $row = $stmt->fetch();

        if (false === $row) {
            return null;
        }

        return $this->rowToEntry($row);
    }

    private function rowToEntry($row)
    {
        return new Entry($row['uuid'], $row['path'], $row['class_fqn']);
    }
}
