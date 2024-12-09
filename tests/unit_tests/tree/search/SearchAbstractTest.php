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
use pvc\struct\tree\search\SearchAbstract;

class SearchAbstractTest extends TestCase
{
    /**
     * @var MockObject|SearchAbstract
     */
    protected $search;

    protected NodeInterface|MockObject $startNodeMock;

    public function setUp(): void
    {

        $this->search = $this->getMockBuilder(SearchAbstract::class)
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
        self::asserttrue(is_callable($this->search->getNodeFilter()));

        $odds = function (int $index) {
            return (1 == $index % 2);
        };

        $this->search->setNodeFilter($odds);
        self::assertEquals($odds, $this->search->getNodeFilter());
    }

    /**
     * testSetGetMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setMaxLevels
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getMaxLevels
     */
    public function testSetGetMaxLevels(): void
    {
        self::assertEquals(PHP_INT_MAX, $this->search->getMaxLevels());
        $newMaxLevels = 3;
        $this->search->setMaxLevels($newMaxLevels);
        self::assertEquals($newMaxLevels, $this->search->getMaxLevels());
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
        $this->search->setMaxLevels($badLevels);
    }

    /**
     * testRewindThrowsExceptionWithStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind()
     */
    public function testRewindThrowsExceptionWithStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->rewind();
    }

    /**
     * testGetStartNodeThrowsExceptionWhenStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     */
    public function testGetStartNodeThrowsExceptionWhenStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->getStartNode();
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getStartNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        $this->search->setStartNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->search->getStartNode());
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
        $this->search->setStartNode($this->startNodeMock);

        /**
         * invalid until the start node has been set (via rewind)
         */
        self::assertFalse($this->search->valid());

        $this->search->setCurrent($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->search->current());
        self::assertEquals($startNodeId, $this->search->key());

        /**
         * now valid after rewinding
         */
        $this->search->rewind();
        self::assertTrue($this->search->valid());

        /**
         * now test unsetting the current node
         */
        $this->search->setCurrent(null);
        self::assertNull($this->search->current());
        self::assertFalse($this->search->valid());
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::rewind
     */
    public function testRewind(): void
    {
        $this->search->setStartNode($this->startNodeMock);
        $this->search->rewind();
        self::assertTrue($this->search->valid());
        self::assertEquals(0, $this->search->getCurrentLevel());
    }
}
