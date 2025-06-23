<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\treesearch;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\treesearch\NodeSearchableInterface;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\err\StartNodeUnsetException;
use pvc\struct\treesearch\SearchAbstract;

class SearchAbstractTest extends TestCase
{
    /**
     * @var MockObject|SearchAbstract
     */
    protected $search;

    protected NodeSearchableInterface|MockObject $startNodeMock;

    public function setUp(): void
    {
        $this->search = $this->getMockBuilder(SearchAbstract::class)
                             ->getMockForAbstractClass();
        $this->startNodeMock = $this->createMock(NodeSearchableInterface::class);
    }

    /**
     * @return void
     * @covers \pvc\struct\treesearch\SearchAbstract::key
     */
    public function testKeyReturnsNullWithNoCurrentNode(): void
    {
        self::assertNull($this->search->key());
    }

    /**
     * testSetMaxLevelsFailsWithBadParameter
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchAbstract::setMaxLevels
     */
    public function testSetMaxLevelsFailsWithBadParameter(): void
    {
        $badLevels = -2;
        self::expectException(SetMaxSearchLevelsException::class);
        $this->search->setMaxLevels($badLevels);
    }

    /**
     * testSetGetMaxLevels
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchAbstract::setMaxLevels
     * @covers \pvc\struct\treesearch\SearchAbstract::getMaxLevels
     */
    public function testSetGetMaxLevels(): void
    {
        self::assertEquals(PHP_INT_MAX, $this->search->getMaxLevels());
        $newMaxLevels = 3;
        $this->search->setMaxLevels($newMaxLevels);
        self::assertEquals($newMaxLevels, $this->search->getMaxLevels());
    }

    /**
     * testRewindThrowsExceptionWithStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\treesearch\SearchAbstract::rewind()
     */
    public function testRewindThrowsExceptionWithStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->rewind();
    }

    /**
     * testGetStartNodeThrowsExceptionWhenStartNodeNotSet
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\treesearch\SearchAbstract::getStartNode
     */
    public function testGetStartNodeThrowsExceptionWhenStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->search->getStartNode();
    }

    /**
     * testSetGetStartNode
     * @covers \pvc\struct\treesearch\SearchAbstract::getStartNode
     * @covers \pvc\struct\treesearch\SearchAbstract::setStartNode
     */
    public function testSetGetStartNode(): void
    {
        $this->search->setStartNode($this->startNodeMock);
        self::assertEquals($this->startNodeMock, $this->search->getStartNode());
    }

    /**
     * @return void
     * @covers \pvc\struct\treesearch\SearchAbstract::current
     */
    public function testCurrentReturnsNullWhenCurrentNodeNotSet(): void
    {
        self::assertNull($this->search->current());
    }

    /**
     * testSetGetCurrentNode
     * @covers \pvc\struct\treesearch\SearchAbstract::setCurrent
     * @covers \pvc\struct\treesearch\SearchAbstract::current
     * @covers \pvc\struct\treesearch\SearchAbstract::key
     * @covers \pvc\struct\treesearch\SearchAbstract::valid
     * @covers \pvc\struct\treesearch\SearchAbstract::rewind
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

        /**
         * now valid after rewinding
         */
        $this->search->rewind();
        self::assertEquals($this->startNodeMock, $this->search->current());
        self::assertEquals($startNodeId, $this->search->key());
        self::assertTrue($this->search->valid());
    }

    /**
     * testRewind
     * @covers \pvc\struct\treesearch\SearchAbstract::rewind
     */
    public function testRewind(): void
    {
        $this->search->setStartNode($this->startNodeMock);
        $this->search->rewind();
        self::assertTrue($this->search->valid());
        self::assertEquals(0, $this->search->getCurrentLevel());
    }

    /**
     * @return void
     * verifies current level is initialized and that getCurrentLevel returns it
     * @covers \pvc\struct\treesearch\SearchAbstract::getCurrentLevel
     */
    public function testInitialState(): void
    {
        self::assertEquals(0, $this->search->getCurrentLevel());
        self::assertNull($this->search->current());
    }
}
