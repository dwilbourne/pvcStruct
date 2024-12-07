<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\dto\factory\TreenodeDTOUnorderedFactory;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;
use pvc\struct\tree\search\NodeMap;
use pvc\struct\tree\search\SearchStrategyDepthFirst;
use pvc\struct\tree\search\SearchStrategyDepthFirstPostorder;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchStrategyDepthFirstPostorderTest extends TestCase
{
    /**
     * @var SearchStrategyDepthFirst
     */
    protected SearchStrategyDepthFirstPostorder $strategy;

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

        $this->strategy = new SearchStrategyDepthFirstPostorder($nodeMap);
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
     * testIteratorPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::rewind
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirstPostorder::getMovementDirection
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirstPostorder::rewind
     */
    public function testIteratorPostOrder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
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
     * testMaxLevelsPostOrder
     * @throws InvalidDepthFirstSearchOrderingException
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::next
     */
    public function testMaxLevelsPostOrder(): void
    {
        $this->strategy->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot();
        $this->strategy->setMaxLevels(3);
        $actualResult = $this->getNodes();
        self::assertEquals($expectedResult, $actualResult);
    }
}
