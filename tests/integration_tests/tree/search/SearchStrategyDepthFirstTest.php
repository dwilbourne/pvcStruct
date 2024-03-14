<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\search\SearchFilterDefault;
use pvc\struct\tree\search\SearchStrategyDepthFirst;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\CollectionUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\NodeTypeUnorderedFactory;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeValueObjectUnorderedFactory;

class SearchStrategyDepthFirstTest extends TestCase
{
    protected SearchStrategyDepthFirst $strategy;

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
        $this->strategy = new SearchStrategyDepthFirst($filter);
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
     * testSetGetOrdering
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::setOrdering
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getOrdering
     */
    public function testSetGetOrdering(): void
    {
        self::assertEquals(SearchStrategyDepthFirst::PREORDER, $this->strategy->getOrdering());
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        self::assertEquals(SearchStrategyDepthFirst::POSTORDER, $this->strategy->getOrdering());
    }

    /**
     * testSetOrderingThrowsExceptionWithBadArgument
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::setOrdering
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::orderingIsValid
     */
    public function testSetOrderingThrowsExceptionWithBadArgument(): void
    {
        self::expectException(InvalidDepthFirstSearchOrderingException::class);
        $this->strategy->setOrdering(3);
    }

    /**
     * testGetNodes
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodes
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodesProtected
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodesRecurse
     */
    public function testGetNodesPreorder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $nodes = $this->strategy->getNodes();
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNodesPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodes
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodesProtected
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodesRecurse
     */
    public function testGetNodesPostOrder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        $nodes = $this->strategy->getNodes();
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNodesOnEmptyTree
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNodesRecurse
     */
    public function testGetNodesOnEmptyTree(): void
    {
        /**
         * no start node set
         */
        self::assertIsArray($this->strategy->getNodes());
        self::assertEmpty($this->strategy->getNodes());
    }

    /**
     * testIteratorPreorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNodeProtected
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::clearVisitCounts
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::clearVisitCountsRecurse
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::current
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::key
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::next
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::valid
     */
    public function testIteratorPreorder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();

        $actualResult = [];
        foreach ($this->strategy as $key => $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test again to verify the clearVisits machinery
         */
        $actualResult = [];
        foreach ($this->strategy as $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testKeyMethod
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::key
     */
    public function testKeyMethod(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        foreach ($this->strategy as $key => $node) {
            self::assertEquals($key, $node->getNodeId());
        }
    }

    /**
     * testSearchFilter
     * @covers \pvc\struct\tree\search\SearchStrategyAbstract::next
     */
    public function testSearchFilter(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $filter = new class implements SearchFilterInterface {
            public function testNode(TreenodeAbstractInterface $node): bool
            {
                return (0 == ($node->getNodeId() % 2));
            }
        };
        $this->strategy->setSearchFilter($filter);

        $expectedResult = [0, 8, 4, 10, 12, 2, 6];

        $actualResult = [];
        foreach ($this->strategy as $node) {
            $actualResult[] = $node->getNodeId();
        }

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testIteratorPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::current
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNodeProtected
     */
    public function testIteratorPostOrder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);

        $nodes = [];
        foreach ($this->strategy as $node) {
            $nodes[] = $node;
        }
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEquals($expectedResult, $actualResult);
    }
}
