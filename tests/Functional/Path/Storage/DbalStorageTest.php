<?php

namespace DTL\DoctrineCR\Tests\Functional\Path\Storage;

use DTL\DoctrineCR\Tests\Functional\BaseTestCase;
use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Page;

/**
 * TODO: Get the storage here.
 */
class DbalStorageTest extends BaseTestCase
{
    /**
     * It should move.
     *
     * @dataProvider provideMove
     */
    public function testMove($description, $pages, $pageSrcPath, $pageDestPath, $expectedEntries)
    {
        $pages = $this->createPages($pages);
        $this->assertArrayHasKey($pageSrcPath, $pages);
        $this->getStorage()->move($pages[$pageSrcPath]->getUuid(), $pageDestPath);
        $this->assertEntries($expectedEntries, $description);
    }

    public function provideMove()
    {
        return [
            [
                'Moving one level down',
                [ 
                    'Parent Page' => [
                        'Child 1' => [
                            'My Page' => [
                            ]
                        ]
                    ]
                ],
                '/Parent Page/Child 1/My Page', '/Parent Page/Foobar',
                [
                    [ 'path' => '/Parent Page', 'depth' => 1 ],
                    [ 'path' => '/Parent Page/Child 1', 'depth' => 2 ],
                    [ 'path' => '/Parent Page/Foobar', 'depth' => 2 ],
                ]
            ],

            [
                'Moving one level up',
                [ 
                    'Parent Page' => [
                        'Child 1' => [
                            'My Page' => [
                            ]
                        ]
                    ],
                    'Foo' => []
                ],
                '/Foo', '/Parent Page/Foobar',
                [
                    [ 'path' => '/Parent Page', 'depth' => 1 ],
                    [ 'path' => '/Parent Page/Child 1', 'depth' => 2 ],
                    [ 'path' => '/Parent Page/Foobar', 'depth' => 2 ],
                    [ 'path' => '/Parent Page/Child 1/My Page', 'depth' => 3 ],
                ]
            ],

            [
                'Moving subtree up',
                [ 
                    'Parent Page' => [
                        'Child 1' => [
                            'My Page' => [],
                        ],
                        'Child 2' => [],
                        'Child 3' => [],
                    ],
                    'Foo' => [
                        'Target' => [
                            'Barbar' => [],
                        ],
                    ],
                ],
                '/Parent Page', '/Foo/Target/Barbar/Voila',
                [
                    [ 'depth' => 1, 'path' => '/Foo' ],
                    [ 'depth' => 2, 'path' => '/Foo/Target' ],
                    [ 'depth' => 3, 'path' => '/Foo/Target/Barbar' ],
                    [ 'depth' => 4, 'path' => '/Foo/Target/Barbar/Voila' ],
                    [ 'depth' => 5, 'path' => '/Foo/Target/Barbar/Voila/Child 1' ],
                    [ 'depth' => 5, 'path' => '/Foo/Target/Barbar/Voila/Child 2' ],
                    [ 'depth' => 5, 'path' => '/Foo/Target/Barbar/Voila/Child 3' ],
                    [ 'depth' => 6, 'path' => '/Foo/Target/Barbar/Voila/Child 1/My Page' ],
                ]
            ],
        ];
    }

    private function assertEntries(array $entryAssertions, $description)
    {
        $stmt = $this->getDbalConnection()->query('SELECT path, depth FROM doctrine_content_repository_paths ORDER by depth');
        $rows = $stmt->fetchAll();

        $rowText = array_reduce($rows, function ($cur, $row) {
            return $cur . sprintf('path: %s, depth: %s' . PHP_EOL, $row['path'], $row['depth']);
        });

        foreach ($entryAssertions as $index => $entryAssertion) {
            $this->assertArrayHasKey($index, $rows, 'Row exists');
            $row = $rows[$index];
            foreach ($entryAssertion as $colName => $expectedValue) {
                $this->assertEquals($expectedValue, $row[$colName], sprintf(
                    $description . ': Asserting %s is "%s" on index "%s" in ' . PHP_EOL . '%s',
                    $colName,
                    $expectedValue,
                    $index,
                    $rowText
                ));
            }
        }
    }

    private function getStorage()
    {
        return $this->getContainer()->offsetGet('dcr.path.storage.dbal');
    }

    private function createPages(array $pageNameTree)
    {
        $this->doCreatePages($pageNameTree, null, $pages);
        foreach ($pages as $page) {
        }
        return $pages;
    }

    private function doCreatePages(array $pageNameTree, $parent = null, &$pages = [])
    {
        foreach ($pageNameTree as $pageName => $children) {
            $page = new Page();
            $page->setTitle($pageName);
            $page->setParent($parent);
            $this->getEntityManager()->persist($page);
            $pages[$page->getPath()] = $page;

            if ($children) {
                $this->doCreatePages($children, $page, $pages);
            }

        }

        $this->getEntityManager()->flush();
    }
}
