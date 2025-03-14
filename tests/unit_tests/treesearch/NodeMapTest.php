<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\treesearch;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\treesearch\NodeVisitableInterface;
use pvc\struct\treesearch\NodeMap;

/**
 * Class NodeMapTest
 */
class NodeMapTest extends TestCase
{
    /**
     * @var NodeMap
     */
    protected NodeMap $map;

    protected NodeVisitableInterface|MockObject $rootNode;
    protected int $rootNodeId;

    protected NodeVisitableInterface|MockObject $childNode;

    protected int $childNodeId;

    protected int $nonExistentNodeId;

    protected array $expectedNodeMapArray;


    public function setUp(): void
    {
        $this->rootNodeId = 0;
        $this->childNodeId = 1;
        $this->rootNode = $this->createMock(NodeVisitableInterface::class);
        $this->childNode = $this->createMock(NodeVisitableInterface::class);
        $this->rootNode->method('getNodeId')->willReturn($this->rootNodeId);
        $this->childNode->method('getNodeId')->willReturn($this->childNodeId);

        $this->map = new NodeMap();

        $this->map->initialize($this->rootNode);
        $this->map->setNode($this->childNode, $this->rootNodeId);
        $this->nonExistentNodeId = 100;
        $this->expectedNodeMapArray = [
            $this->rootNodeId => ['parentId' => null, 'node' => $this->rootNode],
            $this->childNodeId => ['parentId' => $this->rootNodeId, 'node' => $this->childNode],
        ];
    }

    /**
     * testInitialize
     * @covers \pvc\struct\treesearch\NodeMap::initialize
     */
    public function testInitialize(): void
    {
        self::assertEquals($this->rootNode, $this->map->getNode($this->rootNodeId));
    }

    /**
     * testSetGet
     * @covers \pvc\struct\treesearch\NodeMap::setNode
     * @covers \pvc\struct\treesearch\NodeMap::getNode
     * @covers \pvc\struct\treesearch\NodeMap::getParentId
     * @covers \pvc\struct\treesearch\NodeMap::getParent
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

    /**
     * @return void
     * @covers \pvc\struct\treesearch\NodeMap::getNodeMapArray
     */
    public function testGetNodeMapArray(): void
    {
        self::assertEquals($this->expectedNodeMapArray, $this->map->getNodeMapArray());
    }
}
