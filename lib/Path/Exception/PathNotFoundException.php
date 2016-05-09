<?php

namespace DoctrineCr\Path\Exception;

class PathNotFoundException extends \Exception
{
    private $path;

    public function __construct($path)
    {
        parent::__construct(sprintf(
            'Path "%s" not found',
            $path
        ));
        $this->path = $path;
    }

    public function getPath() 
    {
        return $this->path;
    }
    
}
