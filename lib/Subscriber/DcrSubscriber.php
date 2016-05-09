<?php

namespace DoctrineCr\Subscriber;

use DoctrineCr\Path\PathManager;
use Metadata\MetadataFactory;

class DcrSubscriber extends AbstractDcrSubscriber
{
    private $pathManager;

    public function __construct(
        PathManager $pathManager, 
        MetadataFactory $metadataFactory
    )
    {
        parent::__construct($metadataFactory);
        $this->pathManager = $pathManager;
    }

    protected function getPathManager()
    {
        return $this->pathManager;
    }
}
