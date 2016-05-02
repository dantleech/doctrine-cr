<?php

namespace DTL\DoctrineCR\Path\Exception;

class UuidNotFoundException extends NotFoundException
{
    public function __construct($path)
    {
        parent::__construct(sprintf(
            'UUID "%s" not found',
            $path
        ));
    }
}
