<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\err\StartNodeUnsetException;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;
use pvc\struct\tree\node_value_object\factory\TreenodeValueObjectUnorderedFactory;
use pvc\struct\tree\search\NodeDepthMap;
use pvc\struct\tree\search\SearchStrategyDepthFirst;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchStrategyDepthFirstTest extends TestCase
{
    /**
     * @var SearchStrategyDepthFirst
     */
    protected SearchStrategyDepthFirst $strategy;

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
        $fixtureDepthMap = new NodeDepthMap();
        $factory = new TreenodeValueObjectUnorderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory, $fixtureDepthMap);

        $collectionFactory = new CollectionUnorderedFactory();
        $treenodeFactory = new TreenodeUnorderedFactory($collectionFactory);

        $handler = $this->createMock(TreeAbstractEventHandlerInterface::class);
        $this->tree = new TreeUnordered($this->fixture->getTreeId(), $treenodeFactory, $handler);
        $this->tree->hydrate($this->fixture->makeValueObjectArray());

        $strategyDepthMap = new NodeDepthMap();
        $this->strategy = new SearchStrategyDepthFirst($strategyDepthMap);
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
     * testIteratorPreorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::nextPreorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::clearVisitStatusRecurse
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::addChildOfCurrentToDepthNodeMap
     */
    public function testIteratorPreorder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPreorderDepthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test it again to make sure the rewind machinery is working
         */
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testIteratorPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::nextPostorder
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::addChildOfCurrentToDepthNodeMap
     */
    public function testIteratorPostOrder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $this->strategy->setOrdering(SearchStrategyDepthFirst::POSTORDER);
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testUnsetStartNodeThrowsException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     */
    public function testUnsetStartNodeThrowsException(): void
    {
        /**
         * no start node set
         */
        self::expectException(StartNodeUnsetException::class);
        $nodes = $this->getNodes();
        unset($nodes);
    }
}
