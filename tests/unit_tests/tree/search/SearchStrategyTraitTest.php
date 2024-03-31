<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\search\SearchStrategyTrait;

class SearchStrategyTraitTest extends TestCase
{
    /**
     * @var MockObject|SearchStrategyTrait
     */
    protected $trait;

    /**
     * @var TreenodeAbstractInterface|MockObject
     */
    protected TreenodeAbstractInterface|MockObject $startNodeMock;

    public function setUp(): void
    {
        $this->trait = $this->getMockForTrait(SearchStrategyTrait::class);
        $this->startNodeMock = $this->createMock(TreenodeAbstractInterface::class);
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::getStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::setStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::startNodeIsSet
     */
    public function testSetGetStartNode(): void
    {
        self::assertFalse($this->trait->startNodeIsSet());
        $this->trait->setStartNode($this->startNodeMock);
        self::assertTrue($this->trait->startNodeIsSet());
        self::assertEquals($this->startNodeMock, $this->trait->getStartNode());
    }

    /**
     * testSetGetCurrentNode
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::setCurrentNode
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::getCurrentNode
     */
    public function testSetGetCurrentNode(): void
    {
        $this->trait->setCurrentNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->trait->getCurrentNode());
    }

    /**
     * testSetGetNodeDepthMap
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::setNodeDepthMap
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::getNodeDepthMap
     */
    public function testSetGetNodeDepthMap(): void
    {
        $mockMap = $this->createMock(NodeDepthMap::class);
        $this->trait->setNodeDepthMap($mockMap);
        self::assertEquals($mockMap, $this->trait->getNodeDepthMap());
    }

    /**
     * testCurrent
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::current
     */
    public function testCurrent(): void
    {
        $this->trait->setCurrentNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->trait->current());
    }

    /**
     * testKey
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::key
     */
    public function testKey(): void
    {
        $testNodeId = 4;
        $this->trait->setCurrentNode($this->startNodeMock);
        $this->startNodeMock->expects($this->once())->method('getNodeId')->willReturn($testNodeId);
        self::assertEquals($testNodeId, $this->trait->key());
    }

    /**
     * testValidInitializedToFalse
     * @covers \pvc\struct\tree\search\SearchStrategyTrait::valid
     */
    public function testValidInitializedToFalse(): void
    {
        self::assertFalse($this->trait->valid());
    }
}
