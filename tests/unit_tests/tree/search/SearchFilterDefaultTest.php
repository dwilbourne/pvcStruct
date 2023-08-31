<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\struct\tree\search\SearchFilterDefault;

class SearchFilterDefaultTest extends TestCase
{
    protected SearchFilterInterface $filter;

    public function setUp(): void
    {
        $this->filter = new SearchFilterDefault();
    }

    /**
     * testFilterReturnsTrue
     * @covers \pvc\struct\tree\search\SearchFilterDefault::testNode
     */
    public function testFilterReturnsTrue(): void
    {
        $node = $this->createMock(TreenodeAbstractInterface::class);
        self::assertTrue($this->filter->testNode($node));
    }
}
