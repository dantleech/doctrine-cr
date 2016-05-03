<?php

namespace DTL\DoctrineCR\Tests\Unit\Path;

use DTL\DoctrineCR\Path\Entry;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should get its values.
     */
    public function testGetters()
    {
        $entry = new Entry(
            '1234',
            '/path/to',
            'ClassFqn'
        );

        $this->assertEquals('1234', $entry->getUuid());
        $this->assertEquals('/path/to', $entry->getPath());
        $this->assertEquals('ClassFqn', $entry->getClassFqn());
        $this->assertEquals(2, $entry->getDepth());
    }
}
