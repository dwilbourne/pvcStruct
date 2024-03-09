<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;
use pvc\struct\tree\search\SearchStrategyAbstract;
use pvc\struct\tree\search\SearchStrategyDepthFirst;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectUnorderedFactory;

class SearchStrategyAbstractTest extends TestCase
{
    protected SearchStrategyAbstract $strategy;

    protected TreeAbstractInterface $tree;

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

        $searchFilter = new SearchFilterEvenNumberNodeId();
        $this->strategy = new SearchStrategyDepthFirst($searchFilter);

        $this->strategy->setStartNode($this->tree->getRoot());
    }

    /**
     * testClearVisitCounts
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::clearVisitCounts
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::clearVisitCountsRecurse
     */
    public function testClearVisitCounts(): void
    {
        $nodes = $this->strategy->getNodes();

        $this->strategy->clearVisitCounts();

        $visitCount = 0;
        foreach ($nodes as $node) {
            $visitCount += $node->getVisitCount();
        }
        self::assertEquals(0, $visitCount);
    }

    /**
     * testGetNextNode
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNextNode
     */
    public function testGetNextNode(): void
    {
        $expectedNodeIds = [0, 2, 4, 6, 8, 10, 12];
        $nodeSet = [];
        while ($node = $this->strategy->getNextNode()) {
            $nodeSet[] = $node;
        }
        $actualNodeIds = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodeSet);
        self::assertEqualsCanonicalizing($expectedNodeIds, $actualNodeIds);
    }

    /**
     * testGetNodes
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::getNodes
     *
     */
    public function testGetNodes(): void
    {
        $expectedNodeIds = [0, 2, 4, 6, 8, 10, 12];
        $nodeSet = $this->strategy->getNodes();
        $actualNodeIds = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodeSet);
        self::assertEqualsCanonicalizing($expectedNodeIds, $actualNodeIds);
    }
}
