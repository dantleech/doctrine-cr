<?php

namespace DTL\DoctrineCR\Tests\Functional;

use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Page;

class ContentRepositoryTest extends BaseTestCase
{
    /**
     * It should store an Entity at a given path.
     */
    public function testStoreEntity()
    {
        $entity = new Page();
        $entity->setTitle('Page One');

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
