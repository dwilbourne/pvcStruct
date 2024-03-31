<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\search\NodeDepthMap;

class NodeDepthMapTest extends TestCase
{
    protected NodeDepthMap $map;

    public function setUp(): void
    {
        $this->map = new NodeDepthMap();
    }

    /**
     * testConstruction
     * @covers \pvc\struct\tree\search\NodeDepthMap::isEmpty
     */
    public function testConstruction(): void
    {
        self::assertTrue($this->map->isEmpty());
    }

    /**
     * testSetGetNodeDepth
     * @covers \pvc\struct\tree\search\NodeDepthMap::setNodeDepth
     * @covers \pvc\struct\tree\search\NodeDepthMap::getNodeDepth
     */
    public function testSetGetNodeDepth(): void
    {
        $nodeId = 5;
        $depth = 3;
        $this->map->setNodeDepth($nodeId, $depth);
        self::assertEquals($depth, $this->map->getNodeDepth($nodeId));
    }

    /**
     * testInitialize
     * @covers \pvc\struct\tree\search\NodeDepthMap::initialize
     * @covers \pvc\struct\tree\search\NodeDepthMap::isEmpty
     */
    public function testInitialize(): void
    {
        $nodeId = 5;
        $depth = 3;
        $this->map->setNodeDepth($nodeId, $depth);
        self::assertFalse($this->map->isEmpty());
        $this->map->initialize();
        self::assertTrue($this->map->isEmpty());
    }

    /**
     * testIteration
     * @covers \pvc\struct\tree\search\NodeDepthMap::getIterator
     */
    public function testIteration(): void
    {
        $this->map->setNodeDepth(5, 3);
        $this->map->setNodeDepth(4, 2);
        $expectedResult = [5 => 3, 4 => 2];

        $actualResult = [];
        foreach ($this->map->getIterator() as $item => $value) {
            $actualResult[$item] = $value;
        }

        self::assertEquals($expectedResult, $actualResult);
    }
}
