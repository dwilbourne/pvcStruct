<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\treesearch;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\struct\treesearch\VisitStatus;
use pvc\struct\treesearch\err\SetMaxSearchLevelsException;
use pvc\struct\treesearch\SearchDepthFirstPreorder;
use pvcTests\struct\integration_tests\fixture\TestUtils;
use pvcTests\struct\integration_tests\fixture\TreenodeConfigurationsFixture;

class SearchDepthFirstPreorderTest extends TestCase
{
    protected int $treeId = 1;

    /**
     * @var TreeInterface
     */
    protected TreeInterface $tree;

    /**
     * @var SearchDepthFirstPreorder
     */
    protected SearchDepthFirstPreorder $search;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    protected TestUtils $testUtils;

    public function setUp(): void
    {
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->testUtils = new TestUtils($this->fixture);
        $this->fixture = new TreenodeConfigurationsFixture();
        $this->tree = $this->testUtils->testTreeSetup($this->treeId);
        $inputArray = $this->testUtils->makeDtoArray();
        $this->tree->hydrate($inputArray);
        $this->search = new SearchDepthFirstPreorder();
    }

    /**
     * testConstruct
     * @coversNothing
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(SearchDepthFirstPreorder::class, $this->search);
    }

    /**
     * testRewind
     *
     * @covers \pvc\struct\treesearch\SearchDepthFirst::initializeVisitStatusRecurse
     * @covers \pvc\struct\treesearch\SearchDepthFirst::rewind
     */
    public function testRewind(): void
    {
        $startNode = $this->tree->getRoot();
        $startNode->setVisitStatus(VisitStatus::FULLY_VISITED);
        $this->search->setStartNode($startNode);
        $this->search->rewind();

        /**
         * confirm parent::rewind was called
         */
        self::assertTrue($this->search->valid());
        self::assertEquals(0, $this->search->getCurrentLevel());

        /**
         * confirm the current node is the start node
         */
        self::assertEquals($startNode, $this->search->current());
    }

    /**
     * testIteratorPreorder
     *
     * @covers \pvc\struct\treesearch\SearchDepthFirst::move
     * @covers \pvc\struct\treesearch\SearchDepthFirst::next
     * @covers \pvc\struct\treesearch\SearchDepthFirst::getMovementDirection
     * @covers \pvc\struct\treesearch\SearchDepthFirst::getNextVisitableChild
     * @covers \pvc\struct\treesearch\SearchDepthFirst::getNextNode
     * @covers \pvc\struct\treesearch\SearchDepthFirst::endOfSearch
     * @covers \pvc\struct\treesearch\SearchDepthFirstPreorder
     * @covers \pvc\struct\treesearch\SearchAbstract::invalidate
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testIteratorPreorder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult
            = $this->fixture->makeOrderedPreorderDepthFirstArrayOfAllNodeIds(
        );
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test it again to make sure the rewind machinery really is working
         */
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevelsPreorder
     *
     * @throws SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\SearchDepthFirst::allChildrenFullyVisited
     * @covers \pvc\struct\treesearch\SearchDepthFirst::getMovementDirection
     * @covers \pvc\struct\treesearch\SearchDepthFirst::atMaxLevels
     * @covers \pvc\struct\treesearch\SearchDepthFirstPreorder
     * @covers \pvc\struct\treesearch\SearchAbstract::getNodes
     */
    public function testMaxLevelsPreorder(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult
            = $this->fixture->makeOrderedPreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
        );
        $this->search->setMaxLevels(3);
        $actualResult = TestUtils::getNodeIdsFromNodeArray(
            $this->search->getNodes()
        );
        self::assertEquals($expectedResult, $actualResult);
    }
}
