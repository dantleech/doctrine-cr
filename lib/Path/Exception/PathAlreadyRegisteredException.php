<?php

namespace DTL\DoctrineCR\Path\Exception;

use DTL\DoctrineCR\Path\Entry;

class PathAlreadyRegisteredException extends \RuntimeException
{
    public function __construct(Entry $entry, Entry $existingEntry)
    {
        parent::__construct(sprintf(
            'Path "%s" is already registered to %s (%s) when trying to store "%s" (%s)',
            $entry->getPath(),
            $existingEntry->getUuid(),
            $existingEntry->getClassFqn(),
            $entry->getUuid(),
            $entry->getClassFqn()
        ));
    }
}
