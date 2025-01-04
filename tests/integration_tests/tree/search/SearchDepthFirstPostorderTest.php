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
use pvc\struct\tree\search\SearchDepthFirstPostorder;
use pvc\struct\tree\tree\TreeUnordered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class SearchDepthFirstPostorderTest extends TestCase
{
    /**
     * @var SearchDepthFirstPostorder
     */
    protected SearchDepthFirstPostorder $search;

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

        $this->search = new SearchDepthFirstPostorder($nodeMap);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\search\SearchStrategyDepthFirst::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(SearchDepthFirstPostorder::class, $this->search);
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
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodeIds();
        self::assertEquals($expectedResult, $actualResult);

        /**
         * test that the nodemap has all the nodes in it
         */
        self::assertEquals(count($this->search->getNodeMap()->getNodeMapAsArray()), count($actualResult));
    }

    protected function getNodeIds(): array
    {
        $nodes = [];
        foreach ($this->search as $node) {
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
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot();
        $this->search->setMaxLevels(3);
        $actualResult = $this->getNodeIds();
        self::assertEquals($expectedResult, $actualResult);
    }
}
