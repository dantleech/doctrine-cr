<?php

namespace DoctrineCr\Tests\Functional;

use DoctrineCr\Tests\Functional\Resources\Entity\Page;
use DoctrineCr\Path\Exception\PathAlreadyRegisteredException;
use DoctrineCr\Path\Exception\RegistryException;
use DoctrineCr\Path\Exception\NotFoundException;

class EntityManagerTest extends BaseTestCase
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
        $page1 = $this->createPage('Foobar');
        $page2 = $this->createPage('Barfoo', $page1);
        $page3 = $this->createPage('BarBar');

        $page3->setParent($page2);
        $this->getEntityManager()->persist($page3);

        $this->assertEquals(
            '/Foobar/Barfoo/BarBar', $page3->getPath(), 
            'Path correctly updated after persist'
        );

        $this->getEntityManager()->flush();

        $this->assertEquals(
            '/Foobar/Barfoo/BarBar', $page3->getPath(), 
            'Path correctly updated after flush'
        );
    }

    /**
     * It should explicitly move a document
     */
    public function testExplicitMove()
    {
        $page1 = $this->createPage('Foobar');
        $page2 = $this->createPage('Barfoo', $page1);
        $page3 = $this->createPage('BarBar');

        $this->getEntityManager()->move($page3->getPath(), $page2->getPath() . '/BarBar');
        $this->assertEquals('/Foobar/Barfoo/BarBar', $page3->getPath());

        $persistedEntry = $this->getStorage()->getByUuid($page3->getUuid());
        $this->assertEquals(
            '/BarBar', 
            $persistedEntry->getPath(),
            'Path change has not been persisted'
        );

        $this->getEntityManager()->flush();

        $persistedEntry = $this->getStorage()->getByUuid($page3->getUuid());
        $this->assertEquals(
            '/Foobar/Barfoo/BarBar', 
            $persistedEntry->getPath(),
            'Path change has been persisted after flush'
        );
    }

    /**
     * It should remove the entity and its related path entry.
     */
    public function testRemove()
    {
        $page1 = $this->createPage('Foobar');
        $page2 = $this->createPage('Barfoo', $page1);
        $page3 = $this->createPage('BarBar');

        $this->getEntityManager()->remove($page1);
        $this->getEntityManager()->flush();

        try {
            $this->getStorage()->getByUuid($page1->getUuid());
            $this->fail('UUID still exists after remove');
        } catch (NotFoundException $e) {
        }

        try {
            $this->getStorage()->getByUuid($page2->getUuid());
            $this->fail('UUID of sub-node still exists after remove');
        } catch (NotFoundException $e) {
        }
    }

    /**
     * It should allow a path to be replaced via. a move within a single session.
     */
    public function testReplace()
    {
        $page1 = $this->createPage('Foobar');
        $page2 = $this->createPage('BarFoo');
        $page3 = $this->createPage('BarBar');

        $this->getEntityManager()->remove($page1);
        $this->getEntityManager()->move('/BarFoo', '/Foobar');
        $this->getEntityManager()->flush();

        $page = $this->getEntityManager()->find(null, '/Foobar');
        $this->assertEquals($page2->getUuid(), $page->getUuid());
    }

    private function getStorage()
    {
        return $this->getContainer()->offsetGet('dcr.path.storage.dbal');
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
