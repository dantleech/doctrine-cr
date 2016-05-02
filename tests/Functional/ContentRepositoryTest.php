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
        $page = $this->createPage('Page One');

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
        $this->createPage('Page One');
        $this->getEntityManager()->clear();

        $page = $this->getEntityManager()->find(null, '/Page One');

        $this->assertEquals(
            '/Page One', 
            $page->getPath(),
            'It has hydrated the path property'
        );
    }

    /**
     * It should create children.
     */
    public function testChildren()
    {
        $page = $this->createPage('Parent Page');
        $child1 = $this->createPage('Child 1', $page);
        $child2 = $this->createPage('Child 2', $page);

        $this->assertEquals('/Parent Page/Child 1', $child1->getPath());
        $this->assertEquals('/Parent Page/Child 2', $child2->getPath());

        $this->assertInstanceOf(
            Page::class,
            $child1->getParent(),
            'Child has proxy parent'
        );
        $this->assertEquals(
            'Parent Page',
            $child1->getParent()->getTitle(),
            'Parent proxy has correct title'
        );
    }

    /**
     * ?? What should happen if the path exists?
     *
     * @expectedException \DTL\DoctrineCR\Path\Exception\PathAlreadyRegisteredException
     * @expectedExceptionMessage Path "/Hallo" is already registered to
     */
    public function testExistingPath()
    {
        $this->createPage('Hallo');
        $this->createPage('Hallo');
    }

    private function createPage($name, $parent = null)
    {
        $page = new Page();
        $page->setTitle($name);

        if ($parent) {
            $page->setParent($parent);
        }

        $this->getEntityManager()->persist($page);
        $this->getEntityManager()->flush();

        return $page;
    }
}
