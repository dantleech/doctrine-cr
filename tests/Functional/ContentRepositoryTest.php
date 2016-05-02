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
        $page = $this->createPage();

        $this->assertEquals(
            '/Page One', $page->getPath(),
            'It has updated the path property after flusing'
        );
    }

    /**
     * It should hydrate the path entry fields.
     */
    public function testHydratePathEntryFields()
    {
        $this->createPage();
        $this->getEntityManager()->clear();

        $page = $this->getEntityManager()->find(null, '/Page One');

        $this->assertEquals(
            '/Page One', 
            $page->getPath(),
            'It has hydrated the path property'
        );
    }

    private function createPage()
    {
        $page = new Page();
        $page->setTitle('Page One');

        $this->getEntityManager()->persist($page);
        $this->getEntityManager()->flush();

        return $page;
    }
}
