<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\search\SearchStrategyDepthFirst;

class SearchStrategyDepthFirstTest extends TestCase
{
    /**
     * @var SearchStrategyDepthFirst
     */
    protected SearchStrategyDepthFirst $strategy;

    public function setUp(): void
    {
        $depthMap = $this->createMock(NodeDepthMap::class);
        $this->strategy = new SearchStrategyDepthFirst($depthMap);
    }

    /**
     * testSetGetOrdering
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::setOrdering
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getOrdering
     */
    public function testSetGetOrdering(): void
    {
        self::assertEquals(SearchStrategyDepthFirst::PREORDER, $this->strategy->getOrdering());
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        self::assertEquals(SearchStrategyDepthFirst::POSTORDER, $this->strategy->getOrdering());
    }

    /**
     * testSetOrderingThrowsExceptionWithBadArgument
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::setOrdering
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::orderingIsValid
     */
    public function testSetOrderingThrowsExceptionWithBadArgument(): void
    {
        self::expectException(InvalidDepthFirstSearchOrderingException::class);
        $this->strategy->setOrdering(3);
    }
}
