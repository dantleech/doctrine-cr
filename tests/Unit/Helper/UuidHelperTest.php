<?php

namespace DoctrineCr\Tests\Unit\Helper;

use DoctrineCr\Helper\UuidHelper;

class UuidHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should identify a UUID
     *
     * @dataProvider provideIsUUid
     */
    public function testIsUuid($candidate, $isUuid)
    {
        $result = UuidHelper::isUuid($candidate);

        $this->assertEquals($isUuid, $result);
    }

    public function provideIsUuid()
    {
        return [
            [
                '12345678-1234-5678-1234-567812345678',
                true
            ],
            [
                '/path/to',
                false
            ],
        ];
    }
}
