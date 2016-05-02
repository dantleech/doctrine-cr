<?php

namespace DTL\DoctrineCR\Path\Storage\Dbal;

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class Schema extends BaseSchema
{
    const TABLE_NAME = 'doctrine_content_repository_paths';

    public function __construct()
    {
        parent::__construct();
        $table = $this->createTable(self::TABLE_NAME);
        $table->addColumn('uuid', 'string', [ 'notnull' => true, 'unique' => true, 'length' => 36]);
        $table->addColumn('path', 'string', [ 'notnull' => true, 'unique' => true]);
        $table->addColumn('target_class_fqn', 'string');
        $table->setPrimaryKey(['uuid']);
        $table->addIndex(['uuid', 'path']);
    }
}
