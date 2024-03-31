<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\struct\tree\search\SearchIterator;

class SearchIteratorTest extends TestCase
{
    /**
     * @var SearchFilterInterface|MockObject
     */
    protected SearchFilterInterface|MockObject $filter;

    /**
     * @var SearchStrategyInterface|MockObject
     */
    protected SearchStrategyInterface|MockObject $strategy;
    protected SearchIterator $iterator;

    protected int $nodeId = 1;

    public function setUp(): void
    {
        $this->strategy = $this->createMock(SearchStrategyInterface::class);
        $this->filter = $this->createMock(SearchFilterInterface::class);
        $this->iterator = new SearchIterator($this->strategy, $this->filter);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\SearchIterator::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(SearchIterator::class, $this->iterator);
    }

    /**
     * testKey
     * @covers \pvc\struct\tree\search\SearchIterator::key
     */
    public function testKey(): void
    {
        $this->strategy->expects($this->once())->method('key')->willReturn($this->nodeId);
        self::assertEquals($this->nodeId, $this->iterator->key());
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\SearchIterator::rewind
     */
    public function testRewind(): void
    {
        $this->strategy->expects($this->once())->method('rewind');
        foreach ($this->iterator as $node) {
            $result = [];
        }
        unset($result);
    }

    /**
     * testValid
     * @covers \pvc\struct\tree\search\SearchIterator::valid
     */
    public function testValid(): void
    {
        $this->strategy->expects($this->once())->method('valid');
        foreach ($this->iterator as $node) {
            $result = [];
        }
        unset($result);
    }

    /**
     * testNext
     * @covers \pvc\struct\tree\search\SearchIterator::next
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
        $this->iterator->next();
    }

    /**
     * testCurrent
     * @covers \pvc\struct\tree\search\SearchIterator::current
     */
    public function testCurrent(): void
    {
        $mockNode = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->expects($this->once())->method('current')->willReturn($mockNode);
        self::assertEquals($mockNode, $this->iterator->current());
    }
}
