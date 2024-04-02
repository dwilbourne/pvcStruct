<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\NodeFilterInterface;
use pvc\interfaces\struct\tree\search\NodeSearchStrategyInterface;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\search\NodeSearch;

class NodeSearchTest extends TestCase
{
    protected NodeSearch $search;

    protected NodeFilterInterface $filter;

    protected NodeSearchStrategyInterface $strategy;

    public function setUp(): void
    {
        $this->strategy = $this->createMock(NodeSearchStrategyInterface::class);
        $this->filter = $this->createMock(NodeFilterInterface::class);
        $this->search = new NodeSearch($this->strategy, $this->filter);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\NodeSearch::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(NodeSearch::class, $this->search);
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\NodeSearch::getStartNode
     * @covers \pvc\struct\tree\search\NodeSearch::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        $mockStartNode = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->expects($this->once())->method('getStartNode')->willReturn($mockStartNode);
        $this->strategy->expects($this->once())->method('setStartNode')->with($mockStartNode);
        $this->search->setSearchStrategy($this->strategy);
        $this->search->setStartNode($mockStartNode);
        self::assertEquals($mockStartNode, $this->search->getStartNode());
    }

    /**
     * testSetGetSearchFilter
     * @covers \pvc\struct\tree\search\NodeSearch::setSearchFilter
     * @covers \pvc\struct\tree\search\NodeSearch::getSearchFilter
     */
    public function testSetGetSearchFilter(): void
    {
        /**
         * filter cannot be null - must be initialized at construction
         */
        $filter = $this->createMock(NodeFilterInterface::class);
        $this->search->setSearchFilter($filter);
        self::assertEquals($filter, $this->search->getSearchFilter());
    }

    /**
     * testSetGetSearchStrategy
     * @covers \pvc\struct\tree\search\NodeSearch::setSearchStrategy
     * @covers \pvc\struct\tree\search\NodeSearch::getSearchStrategy
     */
    public function testSetGetSearchStrategy(): void
    {
        $this->search->setSearchStrategy($this->strategy);
        self::assertEquals($this->strategy, $this->search->getSearchStrategy());
    }


    /**
     * testGetNodesThrowsExceptionIfStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\NodeSearch::getNodes
     */
    public function testGetNodesThrowsExceptionIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->getNodes();
    }

    /**
     * testGetNodes
     * @covers \pvc\struct\tree\search\NodeSearch::getNodes
     */
    public function testGetNodes(): void
    {
        $this->strategy->expects($this->once())->method('rewind');
        $this->strategy->method('startNodeIsSet')->willReturn(true);

        $matcherValid = $this->exactly(3);
        $callbackValid = function () use ($matcherValid) {
            return match ($matcherValid->getInvocationCount()) {
                1, 2 => true,
                default => false,
            };
        };
        $this->strategy->expects($matcherValid)->method('valid')->willReturnCallback($callbackValid);

        $matcherCurrent = $this->exactly(2);
        $value1 = $this->createMock(TreenodeAbstractInterface::class);
        $value1->method('getNodeId')->willReturn(1);
        $value2 = $this->createMock(TreenodeAbstractInterface::class);
        $value2->method('getNodeId')->willReturn(2);
        $callbackCurrent = function () use ($matcherCurrent, $value1, $value2) {
            return match ($matcherCurrent->getInvocationCount()) {
                1 => $value1,
                default => $value2,
            };
        };
        $this->strategy->expects($matcherCurrent)->method('current')->willReturnCallback($callbackCurrent);

        $matcherKey = $this->exactly(2);
        $key1 = 1;
        $key2 = 2;
        $callbackKey = function () use ($matcherKey, $key1, $key2) {
            return match ($matcherKey->getInvocationCount()) {
                1 => $key1,
                default => $key2,
            };
        };
        $this->strategy->expects($matcherKey)->method('key')->willReturnCallback($callbackKey);

        $resultArray = [$key1 => $value1, $key2 => $value2];

        self::assertEquals($resultArray, $this->search->getNodes());
    }

    /**
     * testKey
     * @covers \pvc\struct\tree\search\NodeSearch::key
     */
    public function testKey(): void
    {
        $nodeId = 2;
        $this->strategy->expects($this->once())->method('key')->willReturn($nodeId);
        self::assertEquals($nodeId, $this->search->key());
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\NodeSearch::rewind
     */
    public function testRewind(): void
    {
        $this->strategy->expects($this->once())->method('rewind');
        $this->search->rewind();
    }

    /**
     * testValid
     * @covers \pvc\struct\tree\search\NodeSearch::valid
     */
    public function testValid(): void
    {
        $this->strategy->expects($this->once())->method('valid')->willReturn(true);
        self::assertTrue($this->search->valid());
    }

    /**
     * testNext
     * @covers \pvc\struct\tree\search\NodeSearch::next
     */
    public function testNext(): void
    {
        $this->strategy->expects($this->exactly(2))->method('next');
        $mockNode = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->expects($this->exactly(4))->method('current')->willReturn($mockNode);

        $matcherFilter = $this->exactly(2);
        $callbackFilter = function () use ($matcherFilter) {
            return match ($matcherFilter->getInvocationCount()) {
                1 => false,
                default => true,
            };
        };
        $this->filter->expects($matcherFilter)->method('testNode')->willReturnCallback($callbackFilter);
        $this->search->next();
    }

    /**
     * testCurrent
     * @covers \pvc\struct\tree\search\NodeSearch::current
     */
    public function testCurrent(): void
    {
        $mockNode = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->expects($this->once())->method('current')->willReturn($mockNode);
        self::assertEquals($mockNode, $this->search->current());
    }
}
