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
use pvc\struct\tree\search\NodeTravelerTrait;

class NodeTravelerTraitTest extends TestCase
{
    protected MockObject $nodeTravelerTrait;

    protected SearchStrategyInterface|MockObject $strategy;

    public function setUp(): void
    {
        $this->nodeTravelerTrait = $this->getMockForTrait(NodeTravelerTrait::class);
        $this->strategy = $this->createMock(SearchStrategyInterface::class);
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::getStartNode
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        $mockStartNode = $this->createMock(TreenodeAbstractInterface::class);
        $this->strategy->expects($this->once())->method('getStartNode')->willReturn($mockStartNode);
        $this->strategy->expects($this->once())->method('setStartNode')->with($mockStartNode);
        $this->nodeTravelerTrait->setSearchStrategy($this->strategy);
        $this->nodeTravelerTrait->setStartNode($mockStartNode);
        self::assertEquals($mockStartNode, $this->nodeTravelerTrait->getStartNode());
    }

    /**
     * testSetGetSearchFilter
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::setSearchFilter
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::getSearchFilter
     */
    public function testSetGetSearchFilter(): void
    {
        /**
         * filter cannot be null - must be initialized at construction
         */
        $filter = $this->createMock(SearchFilterInterface::class);
        $this->nodeTravelerTrait->setSearchFilter($filter);
        self::assertEquals($filter, $this->nodeTravelerTrait->getSearchFilter());
    }

    /**
     * testSetGetSearchStrategy
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::setSearchStrategy
     * @covers \pvc\struct\tree\search\NodeTravelerTrait::getSearchStrategy
     */
    public function testSetGetSearchStrategy(): void
    {
        $this->nodeTravelerTrait->setSearchStrategy($this->strategy);
        self::assertEquals($this->strategy, $this->nodeTravelerTrait->getSearchStrategy());
    }
}
