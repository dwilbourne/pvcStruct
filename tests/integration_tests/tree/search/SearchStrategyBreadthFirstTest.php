<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactory;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\search\SearchStrategyBreadthFirst;
use pvc\struct\tree\tree\TreeOrdered;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchStrategyBreadthFirstTest extends TestCase
{
    /**
     * @var SearchStrategyBreadthFirst
     */
    protected SearchStrategyBreadthFirst $strategy;

    /**
     * @var NodeDepthMap
     */
    protected NodeDepthMap $depthMap;

    /**
     * @var TreeUnordered
     */
    protected TreeOrdered $tree;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $this->depthMap = new NodeDepthMap();
        $factory = new TreenodeValueObjectOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory, $this->depthMap);

        /** @var CollectionFactoryInterface $collectionFactory */
        $collectionFactory = new CollectionOrderedFactory();
        $treenodeFactory = new TreenodeOrderedFactory($collectionFactory);
        $handler = $this->createMock(TreeAbstractEventHandlerInterface::class);
        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory, $handler);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);

        $this->strategy = new SearchStrategyBreadthFirst($this->depthMap);
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
     * testGetNodesThrowsExceptionIfStartNodeNotSet
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::rewind
     */
    public function testGetNodesThrowsExceptionIfStartNodeNotSet(): void
    {
        self::expectException(StartNodeUnsetException::class);
        $this->getNodes();
    }


    /**
     * getNodes
     * @return array
     */
    protected function getNodes(): array
    {
        $nodes = [];
        foreach ($this->strategy as $node) {
            $nodes[] = $node->getNodeId();
        }
        return $nodes;
    }

    /**
     * testGetFullTree
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     */
    public function testGetFullTree(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodes();
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);

        /**
         * verify the nodeDepthMap works correctly in preorder mode
         */
        $this->fixture->makeNodeDepthMap();
        $expectedResult = $this->fixture->getDepthMap();
        $actualResult = $this->strategy->getNodeDepthMap();

        foreach ($expectedResult->getIterator() as $item => $value) {
            self::assertEquals($value, $actualResult->getNodeDepth($item));
        }
    }

    /**
     * testMaxLevels
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::getNextLevelOfNodes
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::exceededMaxLevels
     */
    public function testMaxLevels(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot();
        $this->strategy->setMaxLevels(3);
        $actualResult = $this->getNodes();
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);

        /**
         * verify the nodeDepthMap works correctly in postorder mode
         */
        $this->fixture->makeNodeDepthMap();
        $expectedResult = $this->fixture->getDepthMap();
        $actualResult = $this->strategy->getNodeDepthMap();

        foreach ($expectedResult->getIterator() as $item => $value) {
            self::assertEquals($value, $actualResult->getNodeDepth($item));
        }
    }
}
