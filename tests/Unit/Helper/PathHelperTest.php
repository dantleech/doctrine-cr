<?php

namespace DoctrineCr\Tests\Unit\Helper;

use DoctrineCr\Helper\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should get the parent path
     *
     * @dataProvider provideParentPath
     */
    public function testParentPath($path, $expected)
    {
        $result = PathHelper::getParentPath($path);
        $this->assertEquals($expected, $result);
    }

    public function provideParentPath()
    {
        return [
            [
                '/foo/bar/baz',
                '/foo/bar'
            ],
            [
                '/',
                '/',
            ]
        ];
    }

    /**
     * It should join segments.
     *
     * @dataProvider provideJoin
     */
    public function testJoin(array $segments, $expected)
    {
        $result = PathHelper::join($segments);
        $this->assertEquals($expected, $result);
    }

    public function provideJoin()
    {
        return [
            [
                [ 'one', 'two', 'three' ],
                '/one/two/three'
            ],
            [
                [ '/one/two', 'two', 'three' ],
                '/one/two/two/three'
            ],
            [
                [ '/one/two', 'two', 'three' ],
                '/one/two/two/three'
            ],
        ];
    }

    /**
     * It should throw an exception on invalid segments.
     *
     * @dataProvider provideJoinInvalid
     */
    public function testJoinInvalid(array $segments, $expectedMessage)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $expectedMessage);
        $result = PathHelper::join($segments);
    }

    public function provideJoinInvalid()
    {
        return [
            [
                [ 'one', '/two', 'three' ],
                'Only the first segment can be absolute. Got element "/two" at position 1'
            ],
        ];
    }

    /**
     * It should get the depth of a given path.
     *
     * @dataProvider provideGetDepth
     */
    public function testGetDepth($path, $expected)
    {
        $result = PathHelper::getDepth($path);
        $this->assertEquals($expected, $result);
    }

    public function provideGetDepth()
    {
        return [
            [ '/', 0 ],
            [ '/foo', 1 ],
            [ '/foo/two', 2 ],
        ];
    }


    /**
     * It should get the depth of a given path.
     *
     * @dataProvider provideGetDepthInvalid
     */
    public function testGetDepthInvalid($path, $expectedMessage)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $expectedMessage);
        PathHelper::getDepth($path);
    }

    public function provideGetDepthInvalid()
    {
        return [
            [ '//', 'Found an empty element in segment "//"' ],
        ];
    }

    /**
     * It should say if a given path is the same or a descedant of it.
     *
     * @dataProvider provideIsSelfOrDescendant
     */
    public function testIsSelfOrDescendant($self, $candidate, $expected)
    {
        $result = PathHelper::isSelfOrDescendant($candidate, $self);
        $this->assertEquals($expected, $result);
    }

    public function provideIsSelfOrDescendant()
    {
        return [
            [ '/foo', '/foo', true ],
            [ '/foo', '/foo/bar', true ],
            [ '/foo', '/foo/bar/boo', true ],
            [ '/bar', '/foo', false ],
            [ '/bar', '/foo/foo', false ],
            [ '/two', '/one/1/2/3/two', false ],
        ];
    }
}
