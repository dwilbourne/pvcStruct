<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\search\Search;

class SearchTest extends TestCase
{
    protected Search $search;

    protected SearchFilterInterface $filter;

    protected SearchStrategyInterface $strategy;

    public function setUp(): void
    {
        $this->strategy = $this->createMock(SearchStrategyInterface::class);
        $this->filter = $this->createMock(SearchFilterInterface::class);
        $this->search = new Search($this->strategy, $this->filter);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\Search::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(Search::class, $this->search);
    }

    /**
     * testGetNodesThrowsExceptionIfStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\Search::getNodes
     */
    public function testGetNodesThrowsExceptionIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->getNodes();
    }

    /**
     * testGetNodes
     * @covers \pvc\struct\tree\search\Search::getNodes
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
}
