<?php

namespace pvcTests\struct\integration_tests\fixture\tree\nodeid_factory;

use PHPUnit\Framework\TestCase;

class NodeIdFactoryTest extends TestCase
{
    /**
     * @return void
     * @covers \pvcTests\struct\integration_tests\fixture\tree\nodeid_factory\NodeIdFactory::getNextNodeId
     */
    public function testFactory(): void
    {
        self::assertEquals(0, NodeIdFactory::getNextNodeId());
        self::assertEquals(1, NodeIdFactory::getNextNodeId());
    }
}
