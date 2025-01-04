<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\NodeSearchableInterface;
use pvc\struct\tree\search\NodeMap;

/**
 * Class NodeMapTest
 */
class NodeMapTest extends TestCase
{
    /**
     * @var NodeMap
     */
    protected NodeMap $map;

    protected NodeSearchableInterface|MockObject $rootNode;
    protected int $rootNodeId;

    protected NodeSearchableInterface|MockObject $childNode;

    protected int $childNodeId;

    protected int $nonExistentNodeId;


    public function setUp(): void
    {
        $this->map = new NodeMap();
        $this->rootNodeId = 0;
        $this->childNodeId = 1;
        $this->rootNode = $this->createMock(NodeSearchableInterface::class);
        $this->childNode = $this->createMock(NodeSearchableInterface::class);
        $this->map->setNode($this->rootNodeId, null, $this->rootNode);
        $this->map->setNode($this->childNodeId, $this->rootNodeId, $this->childNode);
        $this->nonExistentNodeId = 100;
    }

    /**
     * testConstruction
     * @covers \pvc\struct\tree\search\NodeMap::isEmpty
     */
    public function testConstruction(): void
    {
        self::assertInstanceOf(NodeMap::class, $this->map);
    }

    /**
     * testInitialize
     * @covers \pvc\struct\tree\search\NodeMap::initialize
     * @covers \pvc\struct\tree\search\NodeMap::isEmpty
     */
    public function testInitialize(): void
    {
        self::assertFalse($this->map->isEmpty());
        $this->map->initialize();
        self::assertTrue($this->map->isEmpty());
    }

    /**
     * testSetGet
     * @covers \pvc\struct\tree\search\NodeMap::setNode
     * @covers \pvc\struct\tree\search\NodeMap::getNode
     * @covers \pvc\struct\tree\search\NodeMap::getParentId
     * @covers \pvc\struct\tree\search\NodeMap::getParent
     */
    public function testSetGet(): void
    {
        self::assertEquals($this->rootNode, $this->map->getNode($this->rootNodeId));
        self::assertEquals($this->rootNodeId, $this->map->getParentId($this->childNodeId));
        self::assertEquals($this->rootNode, $this->map->getParent($this->childNodeId));

        /**
         * test nulls
         */

        self::assertNull($this->map->getNode($this->nonExistentNodeId));
        self::assertNull($this->map->getParentId($this->nonExistentNodeId));
        self::assertNull($this->map->getParent($this->nonExistentNodeId));
    }
}
