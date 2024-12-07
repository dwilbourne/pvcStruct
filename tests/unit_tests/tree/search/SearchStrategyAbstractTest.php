<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\NodeInterface;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\search\SearchStrategyAbstract;

class SearchStrategyAbstractTest extends TestCase
{
    /**
     * @var MockObject|SearchStrategyAbstract
     */
    protected $strategy;

    protected NodeInterface|MockObject $startNodeMock;

    public function setUp(): void
    {

        $this->strategy = $this->getMockBuilder(SearchStrategyAbstract::class)
                               ->disableOriginalConstructor()
                               ->getMockForAbstractClass();
        $this->startNodeMock = $this->createMock(NodeInterface::class);
    }

    /**
     * testSetGetNodeFilter
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setNodeFilter
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNodeFilter
     */
    public function testSetGetNodeFilter(): void
    {
        /**
         * verify there is a default in place
         */
        self::asserttrue(is_callable($this->strategy->getNodeFilter()));

        $odds = function (int $index) {
            return (1 == $index % 2);
        };

        $this->strategy->setNodeFilter($odds);
        self::assertEquals($odds, $this->strategy->getNodeFilter());
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
     * testRewindThrowsExceptionWithStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind()
     */
    public function testRewindThrowsExceptionWithStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->rewind();
    }

    /**
     * testGetStartNodeThrowsExceptionWhenStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     */
    public function testGetStartNodeThrowsExceptionWhenStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->strategy->getStartNode();
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        $this->strategy->setStartNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->strategy->getStartNode());
    }

    /**
     * testSetGetCurrentNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setCurrent
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::current
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::key
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::valid
     */
    public function testSetGetCurrentNodeAndValid(): void
    {
        $startNodeId = 0;
        $this->startNodeMock->method('getNodeId')->willReturn($startNodeId);
        $this->strategy->setStartNode($this->startNodeMock);

        /**
         * invalid until the start node has been set (via rewind)
         */
        self::assertFalse($this->strategy->valid());

        $this->strategy->setCurrent($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->strategy->current());
        self::assertEquals($startNodeId, $this->strategy->key());

        /**
         * now valid after rewinding
         */
        $this->strategy->rewind();
        self::assertTrue($this->strategy->valid());

        /**
         * now test unsetting the current node
         */
        $this->strategy->setCurrent(null);
        self::assertNull($this->strategy->current());
        self::assertFalse($this->strategy->valid());
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind
     */
    public function testRewind(): void
    {
        $this->strategy->setStartNode($this->startNodeMock);
        $this->strategy->rewind();
        self::assertTrue($this->strategy->valid());
        self::assertEquals(0, $this->strategy->getCurrentLevel());
    }
}
