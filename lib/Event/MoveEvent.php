<?php

namespace DoctrineCr\Event;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Event\ManagerEventArgs;

class MoveEvent extends ManagerEventArgs
{
    private $srcUuid;
    private $destPath;

    public function __construct(ObjectManager $objectManager, $srcUuid, $destPath)
    {
        parent::__construct($objectManager);
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
