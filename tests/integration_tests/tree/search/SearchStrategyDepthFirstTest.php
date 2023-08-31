<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\factory\TreenodeAbstractFactory;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;
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
        $valueValidator = new TreenodeValueValidatorDefault();
        $treenodeFactory = new TreenodeAbstractFactory(
            $nodeTypeFactory,
            $collectionFactory,
            $valueValidator
        );

        $this->tree = new TreeUnordered($this->fixture->getTreeId(), $treenodeFactory);

        $this->valueObjectArray = $this->fixture->makeValueObjectArray();
        $this->tree->hydrate($this->valueObjectArray);

        $filter = new SearchFilterDefault();
        $this->strategy = new SearchStrategyDepthFirst($filter);
        $this->strategy->setStartNode($this->tree->getRoot());
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
     * @throws \pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException
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
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        $nodes = $this->strategy->getNodes();
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNextNodePreorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNode
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNodeProtected
     */
    public function testGetNextNodePreorder(): void
    {
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $nodes = [];
        while ($node = $this->strategy->getNextNode()) {
            $nodes[] = $node;
        }
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testGetNextNodePostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNode
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::getNextNodeProtected
     */
    public function testGetNextNodePostOrder(): void
    {
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        $nodes = [];
        while ($node = $this->strategy->getNextNode()) {
            $nodes[] = $node;
        }
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }

    /**
     * testResetSearch
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::resetSearch
     */
    public function testResetSearch(): void
    {
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $nodes = [];
        while ($node = $this->strategy->getNextNode()) {
            $nodes[] = $node;
        }
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);

        self::assertNull($this->strategy->getNextNode());

        $this->strategy->resetSearch();

        $nodes = [];
        while ($node = $this->strategy->getNextNode()) {
            $nodes[] = $node;
        }
        $actualResult = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($nodes);
        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }
}
