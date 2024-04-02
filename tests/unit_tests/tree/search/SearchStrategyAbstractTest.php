<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\search\SearchStrategyAbstract;

class SearchStrategyAbstractTest extends TestCase
{
    /**
     * @var NodeDepthMap|MockObject
     */
    protected $nodeDepthMap;
    /**
     * @var MockObject|SearchStrategyAbstract
     */
    protected $strategy;

    /**
     * @var TreenodeAbstractInterface|MockObject
     */
    protected TreenodeAbstractInterface|MockObject $startNodeMock;

    public function setUp(): void
    {
        $this->nodeDepthMap = $this->createMock(NodeDepthMap::class);
        $this->strategy = $this->getMockBuilder(SearchStrategyAbstract::class)
                               ->disableOriginalConstructor()
                               ->getMockForAbstractClass();
        $this->startNodeMock = $this->createMock(TreenodeAbstractInterface::class);
    }

    /**
     * testSetGetMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setMaxLevels
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getMaxLevels
     */
    public function testSetGetMaxLevels(): void
    {
        self::assertEquals(PHP_INT_MAX, $this->strategy->getMaxLevels());
        $newMaxLevels = 3;
        $this->strategy->setMaxLevels($newMaxLevels);
        self::assertEquals($newMaxLevels, $this->strategy->getMaxLevels());
    }

    /**
     * testSetMaxLevelsFailsWithBadParameter
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setMaxLevels
     */
    public function testSetMaxLevelsFailsWithBadParameter(): void
    {
        $badLevels = -2;
        self::expectException(BadSearchLevelsException::class);
        $this->strategy->setMaxLevels($badLevels);
    }


    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::startNodeIsSet
     */
    public function testSetGetStartNode(): void
    {
        self::assertFalse($this->strategy->startNodeIsSet());
        $this->strategy->setStartNode($this->startNodeMock);
        self::assertTrue($this->strategy->startNodeIsSet());
        self::assertEquals($this->startNodeMock, $this->strategy->getStartNode());
    }

    /**
     * testSetGetCurrentNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setCurrentNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getCurrentNode
     */
    public function testSetGetCurrentNode(): void
    {
        $this->strategy->setCurrentNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->strategy->getCurrentNode());
    }

    /**
     * testSetGetNodeDepthMap
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setNodeDepthMap
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNodeDepthMap
     */
    public function testSetGetNodeDepthMap(): void
    {
        $this->strategy->setNodeDepthMap($this->nodeDepthMap);
        self::assertEquals($this->nodeDepthMap, $this->strategy->getNodeDepthMap());
    }

    /**
     * testCurrent
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::current
     */
    public function testCurrent(): void
    {
        $this->strategy->setCurrentNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->strategy->current());
    }

    /**
     * testSetGetCurrentLevel
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setCurrentLevel
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getCurrentLevel
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::incrementCurrentLevel
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::decrementCurrentLevel
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::atMaxLevel
     */
    public function testSetGetCurrentLevel(): void
    {
        $testLevel = 7;
        $maxLevel = 9;
        $this->strategy->setMaxLevels($maxLevel);
        $this->strategy->setCurrentLevel($testLevel);
        self::assertEquals($testLevel, $this->strategy->getCurrentLevel());
        $this->strategy->incrementCurrentLevel();
        self::assertEquals($testLevel + 1, $this->strategy->getCurrentLevel());
        self::assertTrue($this->strategy->atMaxLevel());
        $this->strategy->decrementCurrentLevel();
        self::assertEquals($testLevel, $this->strategy->getCurrentLevel());
    }

    /**
     * testKey
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::key
     */
    public function testKey(): void
    {
        $testNodeId = 4;
        $this->strategy->setCurrentNode($this->startNodeMock);
        $this->startNodeMock->expects($this->once())->method('getNodeId')->willReturn($testNodeId);
        self::assertEquals($testNodeId, $this->strategy->key());
    }

    /**
     * testValid
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setValid
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::valid
     */
    public function testValid(): void
    {
        /**
         * initialized to false
         */
        self::assertFalse($this->strategy->valid());
        $this->strategy->setValid(true);
        self::assertTrue($this->strategy->valid());
    }

    /**
     * testRewindThrowsExceptionIfStartNodeNotSet
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind
     */
    public function testRewindThrowsExceptionIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->rewind();
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind
     */
    public function testRewind(): void
    {
        $testNodeId = 4;
        $this->startNodeMock->expects($this->once())->method('getNodeId')->willReturn($testNodeId);
        $this->strategy->setStartNode($this->startNodeMock);
        $this->strategy->setNodeDepthMap($this->nodeDepthMap);
        $this->nodeDepthMap->expects($this->once())->method('initialize');
        $this->nodeDepthMap->expects($this->once())->method('setNodeDepth')->with($testNodeId, 0);
        $this->strategy->rewind();
        self::assertTrue($this->strategy->valid());
        self::assertEquals(0, $this->strategy->getCurrentLevel());
        self::assertEquals($this->startNodeMock, $this->strategy->getCurrentNode());
    }
}
