<?php

namespace DTL\DoctrineCR\Path\Exception;

class PathAlreadyRegisteredException extends \RuntimeException
{
    public function __construct($path, $uuid, $classFqn)
    {
        parent::__construct(sprintf(
            'Path "%s" is already registered to %s (%s)',
            $path, $uuid, $classFqn
        ));
    }
}
