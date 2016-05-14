<?php

namespace DoctrineCr\Path\Storage\Dbal;

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class Schema extends BaseSchema
{
    const TABLE_NAME = 'doctrine_content_repository_paths';

    public function __construct()
    {
        parent::__construct();
        $table = $this->createTable(self::TABLE_NAME);
        $table->addColumn('uuid', 'string', [ 'notnull' => true, 'length' => 36]);
        $table->addColumn('path', 'string', [ 'notnull' => true ]);
        $table->addColumn('class_fqn', 'string');
        $table->addColumn('depth', 'integer');
        $table->setPrimaryKey(['uuid']);
        $table->addUniqueIndex(['path', 'uuid']);
    }

    public function mergeTo(BaseSchema $schema) 
    {
        foreach ($this->getTables() as $table) {
            $schema->_addTable($table);
        }

        foreach ($this->getSequences() as $sequence) {
            $schema->_addSequence($sequence);
        }
    }
}
