<?php

namespace DTL\DoctrineCR\Path\Storage;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidFactory;
use DTL\DoctrineCR\Path\StorageInterface;
use DTL\DoctrineCR\Exception\PathNotFoundException;
use DTL\DoctrineCR\Path\Storage\Dbal\Schema;
use DTL\DoctrineCR\Path\Entry;

/**\
 * TODO: Rename lookUp[Uuid|Path] to lookupBy[Uuid|Path]
 */
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
    public function lookUpUuid($path)
    {
        return $this->lookUp('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function lookUpPath($uuid)
    {
        return $this->lookUp('uuid', $uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function register($path, $targetClassFqn)
    {
        // TODO: Handle existing paths in a performant way..
        // TODO: Validate path
        $sql = sprintf(
            'INSERT INTO %s (uuid, path, target_class_fqn) VALUES (?, ?, ?)',
            Schema::TABLE_NAME
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            $uuid = (string) $this->uuidFactory->uuid4(),
            $path,
            $targetClassFqn
        ]);

        return new Entry($uuid, $path, $targetClassFqn);
    }

    private function lookUp($columnName, $identifier)
    {
        $sql = sprintf(
            'SELECT uuid, path, target_class_fqn FROM %s WHERE %s = ?',
            Schema::TABLE_NAME,
            $columnName
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([ $identifier ]);
        $row = $stmt->fetch();

        if (null === $row) {
            throw new PathNotFoundException($path);
        }

        return new Entry($row['uuid'], $row['path'], $row['target_class_fqn']);
    }
}
