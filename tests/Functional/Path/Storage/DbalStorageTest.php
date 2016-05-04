<?php

namespace DTL\DoctrineCR\Tests\Functional\Path\Storage;

use DTL\DoctrineCR\Tests\Functional\BaseTestCase;

/**
 * TODO: Get the storage here.
 */
class DbalStorageTest extends BaseTestCase
{
    /**
     * It should move deeper
     */
    public function testMove()
    {
        $page = $this->createPage('Parent Page');
        $child1 = $this->createPage('Child 1', $page);
        $page2 = $this->createPage('My Page');
        $page2->setParent($child1);

        $this->getEntityManager()->persist($page2);
        $this->getEntityManager()->flush();

        $this->assertPaths([
            [ 'path' => '/Parent Page', 'depth' => 1 ],
            [ 'path' => '/Parent Page/Child 1', 'depth' => 2 ],
            [ 'path' => '/Parent Page/Child 1/My Page', 'depth' => 3 ],
        ]);
    }

    /**
     * It should move shallower
     */
    public function testMoveShallower()
    {
        $page = $this->createPage('Parent Page');
        $child1 = $this->createPage('Child 1', $page);
        $page2 = $this->createPage('My Page', $child1);

        $this->assertPaths([
            [ 'path' => '/Parent Page', 'depth' => 1 ],
            [ 'path' => '/Parent Page/Child 1', 'depth' => 2 ],
            [ 'path' => '/Parent Page/Child 1/My Page', 'depth' => 3 ],
        ]);

        $child1->setParent(null);
        $this->getEntityManager()->persist($child1);
        $this->getEntityManager()->flush();

        $this->assertPaths([
            [ 'path' => '/Parent Page', 'depth' => 1 ],
            [ 'path' => '/Child 1', 'depth' => 1 ],
            [ 'path' => '/Child 1/My Page', 'depth' => 2 ],
        ]);

        $page2 = $this->createPage('My Page');
        $page2->setParent($page2);
        $this->getEntityManager()->persist($page2);
        $this->getEntityManager()->flush();
   }

    private function assertPaths(array $entryAssertions)
    {
        $stmt = $this->getConnection()->query('SELECT path, depth FROM doctrine_content_repository_paths ORDER by depth');
        $rows = $stmt->fetchAll();

        foreach ($entryAssertions as $index => $entryAssertion) {
            $this->assertArrayHasKey($index, $rows, 'Row exists');
            $row = $rows[$index];
            foreach ($entryAssertion as $colName => $expectedValue) {
                $this->assertEquals($expectedValue, $row[$colName]);
            }
        }
    }
}
