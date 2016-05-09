<?php

namespace DoctrineCr\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\ObjectManager;

class MoveEvent extends EventArgs
{
    private $srcUuid;
    private $destPath;

    public function __construct(ObjectManager $objectManager, $srcUuid, $destPath)
    {
        $this->srcUuid = $srcUuid;
        $this->destPath = $destPath;
    }

    public function getSrcUuid() 
    {
        return $this->srcUuid;
    }

    public function getDestPath() 
    {
        return $this->destPath;
    }
}
