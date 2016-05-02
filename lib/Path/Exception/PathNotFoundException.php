<?php

namespace DTL\DoctrineCR\Exception;

class PathNotFoundException extends \Exception
{
    public function __construct($path)
    {
        parent::__construct(sprintf(
            'Path "%s" not found',
            $path
        ));
    }
}
