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
    public function getByPath($path)
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
    public function getByUuid($uuid)
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
        $entry = $this->getByUuid($uuid);
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
    public function commit(Entry $entry)
    {
        try {
            $existingEntry = $this->getByPath($entry->getPath());
            throw new PathAlreadyRegisteredException(
                $entry,
                $existingEntry
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
            $entry->getUuid(),
            $entry->getPath(),
            $entry->getClassFqn(),
            PathHelper::getDepth($entry->getPath())
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function remove($uuid)
    {
        $entry = $this->getByUuid($uuid);

        $sql = sprintf(
            'DELETE FROM %s WHERE path LIKE :match OR uuid = :uuid',
            Schema::TABLE_NAME
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([ 
            'match' => $entry->getPath() . '/%',
            'uuid' => $entry->getUuid()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function move($uuid, $destPath)
    {
        $entry = $this->getByUuid($uuid);
        $platform = $this->connection->getDatabasePlatform();

        $sql = sprintf(
            'UPDATE %s SET path = %s, depth = depth + (:destDepth - %d) WHERE path LIKE :match OR path = :srcPath',
            Schema::TABLE_NAME,
            $platform->getConcatExpression(
                ':destPath',
                $platform->getSubstringExpression(
                    'path', 
                    $platform->getLengthExpression(':srcPath') . ' + 1'
                )
            ),
            (int) $entry->getDepth() // for some reason I cannot bind this value, but it is always an integer.
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([ 
            'destPath' => $destPath,
            'destDepth' => PathHelper::getDepth($destPath),
            'match' => $entry->getPath() .'/%',
            'srcPath' => $entry->getPath(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commitTransaction()
    {
        $this->connection->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollbackTransaction()
    {
        $this->connection->rollBack();
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
