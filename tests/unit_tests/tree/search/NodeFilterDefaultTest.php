<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\NodeFilterInterface;
use pvc\struct\tree\search\NodeFilterDefault;

class NodeFilterDefaultTest extends TestCase
{
    protected NodeFilterInterface $filter;

    public function setUp(): void
    {
        $this->filter = new NodeFilterDefault();
    }

    /**
     * testFilterReturnsTrue
     * @covers \pvc\struct\tree\search\NodeFilterDefault::testNode
     */
    public function testFilterReturnsTrue(): void
    {
        $node = $this->createMock(TreenodeAbstractInterface::class);
        self::assertTrue($this->filter->testNode($node));
    }
}
