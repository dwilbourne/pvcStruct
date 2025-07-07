<?php

namespace pvcTests\struct\unit_tests\tree\node;

use pvc\struct\tree\node\NodeIdFactory;
use PHPUnit\Framework\TestCase;

class NodeIdFactoryTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\tree\node\NodeIdFactory::getNextNodeId
     */
    public function testFactory(): void
    {
        self::assertEquals(0, NodeIdFactory::getNextNodeId());
        self::assertEquals(1, NodeIdFactory::getNextNodeId());
    }
}
