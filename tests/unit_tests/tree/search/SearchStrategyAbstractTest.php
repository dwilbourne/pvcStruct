<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\search\SearchStrategyAbstract;

class SearchStrategyAbstractTest extends TestCase
{
    protected SearchStrategyAbstract $strategy;

    public function setUp(): void
    {
        $this->strategy = $this->getMockBuilder(SearchStrategyAbstract::class)
                               ->disableOriginalConstructor()
                               ->getMockForAbstractClass();
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        self::assertNull($this->strategy->getStartNode());
        $startNodeMock = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->setStartNode($startNodeMock);
        self::assertEquals($startNodeMock, $this->strategy->getStartNode());
    }

    /**
     * testSetGetSearchFilter
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setSearchFilter
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getSearchFilter
     */
    public function testSetGetSearchFilter(): void
    {
        $filter = $this->createMock(SearchFilterInterface::class);
        $this->strategy->setSearchFilter($filter);
        self::assertEquals($filter, $this->strategy->getSearchFilter());
    }

    /**
     * testGetNodesFailsIfStartnodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNodes
     */
    public function testGetNodesFailsIfStartnodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->getNodes();
    }

    /**
     * testGetNextNodeFailsIfStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNextNode
     */
    public function testGetNextNodeFailsIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->getNextNode();
    }

    /**
     * testClearVisitCountFailsIfStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::clearVisitCounts
     */
    public function testClearVisitCountFailsIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->clearVisitCounts();
    }
}
