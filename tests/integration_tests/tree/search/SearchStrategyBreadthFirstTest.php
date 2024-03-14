<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\search\SearchFilterDefault;
use pvc\struct\tree\search\SearchStrategyBreadthFirst;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectUnorderedFactory;

class SearchStrategyBreadthFirstTest extends TestCase
{
    protected SearchStrategyBreadthFirst $strategy;

    protected TreeUnordered $tree;

    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $factory = new TreenodeValueObjectUnorderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        $collectionFactory = new CollectionUnorderedFactory();
        $nodeTypeFactory = new NodeTypeUnorderedFactory();
        $treenodeFactory = new TreenodeAbstractFactory(
            $nodeTypeFactory,
            $collectionFactory
        );
        $handler = $this->createMock(TreeAbstractEventHandlerInterface::class);
        $this->tree = new TreeUnordered($this->fixture->getTreeId(), $treenodeFactory, $handler);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);

        $filter = new SearchFilterDefault();
        $this->strategy = new SearchStrategyBreadthFirst($filter);
        $this->strategy->setStartNode($this->tree->getRoot());
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(SearchStrategyBreadthFirst::class, $this->strategy);
    }

    /**
     * testSetGetMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::setMaxLevels
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getMaxLevels
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
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::setMaxLevels
     */
    public function testSetMaxLevelsFailsWithBadParameter(): void
    {
        $badLevels = -2;
        self::expectException(BadSearchLevelsException::class);
        $this->strategy->setMaxLevels($badLevels);
    }

    /**
     * testGetNodes
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodes
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodesProtected
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodesRecurse
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     */
    public function testGetNodesFullTree(): void
    {
        $expectedResult = $this->fixture->makeUnorderedBreadthFirstArrayOfAllNodeIds();
        $nodes = $this->strategy->getNodes();
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNodesWithMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodes
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodesProtected
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNodesRecurse
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     */
    public function testGetNodesWithMaxLevels(): void
    {
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayTwoLevelsStartingAtRoot();
        $this->strategy->setMaxLevels(2);
        $nodes = $this->strategy->getNodes();
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNextNodeFullTree
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::current
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextNodeProtected
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     */
    public function testGetNextNodeFullTree(): void
    {
        $expectedResult = $this->fixture->makeUnorderedBreadthFirstArrayOfAllNodeIds();

        $actualResult = [];
        foreach ($this->strategy as $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test rewind machinery by running the test again
         */
        $actualResult = [];
        foreach ($this->strategy as $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * getNextNodeWithMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::current
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextNodeProtected
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     */
    public function testGetNextNodeWithMaxLevels(): void
    {
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayTwoLevelsStartingAtRoot();
        $this->strategy->setMaxLevels(2);
        $actualResult = [];
        foreach ($this->strategy as $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }
}
