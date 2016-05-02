<?php

namespace DTL\DoctrineCR\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DTL\DoctrineCR\Path\StorageInterface;

class CRSubscriber implements EventSubscriber
{
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        return;
        $object = $args->getObject();
        $metadata = $this->metadataFactory->getMetadataFor(ClassUtils::getRealClass(get_class($object)));

        $parent = $metadata->getFieldValue($object, $metadata->getParentField());
        $uuid = $metadata->getFieldValue($object, $metadata->getUuidField());

    }
}
