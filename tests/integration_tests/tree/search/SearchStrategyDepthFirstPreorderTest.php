<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\NodeInterface;
use pvc\interfaces\struct\tree\search\VisitStatus;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\dto\factory\TreenodeDTOUnorderedFactory;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;
use pvc\struct\tree\search\NodeMap;
use pvc\struct\tree\search\SearchStrategyDepthFirst;
use pvc\struct\tree\search\SearchStrategyDepthFirstPreorder;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchStrategyDepthFirstPreorderTest extends TestCase
{
    /**
     * @var SearchStrategyDepthFirstPreorder
     */
    protected SearchStrategyDepthFirstPreorder $strategy;

    /**
     * @var TreeUnordered
     */
    protected TreeUnordered $tree;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $factory = new TreenodeDTOUnorderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        $collectionFactory = new CollectionUnorderedFactory();
        $treenodeFactory = new TreenodeUnorderedFactory($collectionFactory);

        $this->tree = new TreeUnordered($this->fixture->getTreeId(), $treenodeFactory);
        $this->tree->hydrate($this->fixture->makeDTOArray());

        $nodeMap = new NodeMap();

        $this->strategy = new SearchStrategyDepthFirstPreorder($nodeMap);
    }

    protected function getNodes(): array
    {
        $nodes = [];
        foreach ($this->strategy as $node) {
            $nodes[] = $node->getNodeId();
        }
        return $nodes;
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(SearchStrategyDepthFirst::class, $this->strategy);
    }

    /**
     * testRewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::initializeVisitStatusRecurse
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getCurrentLevel
     */
    public function testRewind(): void
    {
        $startNode = $this->tree->getRoot();
        $startNode->setVisitStatus(VisitStatus::FULLY_VISITED);
        $this->strategy->setStartNode($startNode);
        $this->strategy->rewind();

        /**
         * confirm parent::rewind was called
         */
        self::assertTrue($this->strategy->valid());
        self::assertEquals(0, $this->strategy->getCurrentLevel());

        /**
         * confirm the current node is the start node
         */
        self::assertEquals($startNode, $this->strategy->current());
    }

    /**
     * testIteratorPreorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::move
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getMovementDirection
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextVisitableChild
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getParent
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::endOfSearch
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::shouldStop
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirstPreorder::getMovementDirection
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirstPreorder::updateVisitStatus
     */
    public function testIteratorPreorder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test it again to make sure the rewind machinery really is working
         */
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testMaxLevelsPreorder
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::allChildrenFullyVisited
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getMovementDirection
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::updateVisitStatus
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::atMaxLevels
     */
    public function testMaxLevelsPreorder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makePreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot();
        $this->strategy->setMaxLevels(3);
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testNodeFiltering
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     */
    public function testNodeFiltering(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        /** @phpcs:ignore */
        $expectedResult = $this->fixture->makePreorderDepthFirstArrayThreeLevelsDeepStartingAtRootForEvenNumberedNodes(
        );
        $evens = function (NodeInterface $node) {
            return (0 == $node->getNodeId() % 2);
        };
        $this->strategy->setNodeFilter($evens);
        $this->strategy->setMaxLevels(3);
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }
}
