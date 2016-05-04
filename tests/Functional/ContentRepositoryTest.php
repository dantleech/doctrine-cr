<?php

namespace DTL\DoctrineCR\Tests\Functional;

use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Page;
use DTL\DoctrineCR\Path\Exception\PathAlreadyRegisteredException;
use DTL\DoctrineCR\Path\Exception\RegistryException;

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
     * It should set the parent object.
     */
    public function testParent()
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
     * It should map children.
     */
    public function testChildren()
    {
        $page = $this->createPage('Parent Page');
        $child1 = $this->createPage('Child 1', $page);
        $child2 = $this->createPage('Child 2', $page);

        // TODO: hydrate after persist/flush
        $this->getEntityManager()->refresh($page);

        $children = $page->getChildren();
        $this->assertCount(2, $children);

        $this->assertSame($child1, $children[0]);
        $this->assertSame($child2, $children[1]);

        $this->getEntityManager()->clear();

        $page = $this->getEntityManager()->find(null, '/Parent Page');

        $children = $page->getChildren();
        $this->assertCount(2, $children);

        $this->assertNotSame($child1, $children[0]);

        $this->assertEquals('Child 1', $children[0]->getTitle());
        $this->assertEquals('Child 2', $children[1]->getTitle());
    }

    /**
     * It should map the depth.
     */
    public function testMapDepth()
    {
        $page = $this->createPage('Parent Page');
        $child1 = $this->createPage('Child 1', $page);
        $child2 = $this->createPage('Child 2', $page);

        // TODO: hydrate after persist/flush
        $this->getEntityManager()->refresh($page);
        $this->getEntityManager()->refresh($child1);
        $this->getEntityManager()->refresh($child2);

        $this->assertEquals(1, $page->getDepth());
        $this->assertEquals(2, $child1->getDepth());
        $this->assertEquals(2, $child2->getDepth());
    }

    /**
     * ?? What should happen if the path exists?
     */
    public function testExistingPath()
    {
        try {
            $this->createPage('Hallo');
            $this->createPage('Hallo');
        } catch (\Exception $e) {
            $this->assertInstanceOf(RegistryException::class, $e);
        }
    }

    /**
     * It should implicitly change the path when changing the parent.
     *
     * TODO: This path stuff must be moved to the dbal storage test.
     */
    public function testImplicitMove()
    {
    }
}
