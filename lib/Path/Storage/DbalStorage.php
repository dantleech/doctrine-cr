<?php

namespace DTL\DoctrineCR\Path\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\UuidFactory;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Exception\PathNotFoundException;

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
    public function lookUpPath($path)
    {
        return $this->lookUp($sql, 'path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function lookUpUuid($uuid)
    {
        return $this->lookUp($sql, 'uuid', $uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function store($path, $targetClassFqn)
    {
        $sql = sprintf(
            'INSERT INTO %s uuid, path, target_class_fqn VALUES (?, ?, ?, ?)',
            Schema::TABLE_NAME
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            (string) $this->uuidFactory->uuid4(),
            $path,
            $targetClassFqn
        ]);
    }

    private function lookUp($sql, $columnName, $identifier)
    {
        $sql = sprintf(
            'SELECT uuid, path, target_class_fqn FROM %s WHERE %s = ?',
            Schema::TABLE_NAME,
            $columnName
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([ $path ]);
        $row = $stmt->fetch();

        if (null === $row) {
            throw new PathNotFoundException($path);
        }

        return new PathEntry($row[0], $row[1], $row[2], $row[3]);
    }
}
