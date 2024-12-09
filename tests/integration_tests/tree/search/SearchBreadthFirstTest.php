<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\integration_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\search\NodeInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\dto\factory\TreenodeDTOOrderedFactory;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\search\SearchBreadthFirst;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

/**
 * Class SearchBreadthFirstTest
 * @template PayloadType of HasPayloadInterface
 */
class SearchBreadthFirstTest extends TestCase
{
    /**
     * @var TreeOrdered<PayloadType>
     */
    protected TreeOrdered $tree;

    /**
     * @var SearchBreadthFirst
     */
    protected SearchBreadthFirst $search;

    /**
     * @var TreenodeConfigurationsFixture
     */
    protected TreenodeConfigurationsFixture $fixture;

    public function setUp(): void
    {
        $factory = new TreenodeDTOOrderedFactory();
        $this->fixture = new TreenodeConfigurationsFixture($factory);

        /** @var CollectionFactoryInterface $collectionFactory */
        $collectionFactory = new CollectionOrderedFactory();
        $treenodeFactory = new TreenodeOrderedFactory($collectionFactory);
        $this->tree = new TreeOrdered($this->fixture->getTreeId(), $treenodeFactory);
        $this->tree->hydrate($this->fixture->makeDTOArray());

        $this->search = new SearchBreadthFirst();
    }

    /**
     * getNodeIds
     * @return array
     */
    protected function getNodeIds(): array
    {
        $nodes = [];
        foreach ($this->search as $node) {
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
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayOfAllNodeIds();
        $actualResult = $this->getNodeIds();
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
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
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot();
        $this->search->setMaxLevels(3);
        $actualResult = $this->getNodeIds();
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testNodeFiltering
     * @throws BadSearchLevelsException
     * @covers \pvc\struct\tree\search\SearchStrategyBreadthFirst::next
     */
    public function testNodeFiltering(): void
    {
        $this->search->setStartNode($this->tree->getRoot());
        $expectedResult = $this->fixture->makeOrderedBreadthFirstArrayThreeLevelsStartingAtRootForEvenNumberedNodes();
        $this->search->setMaxLevels(3);
        $evens = function (NodeInterface $node) {
            return (0 == $node->getNodeId() % 2);
        };
        $this->search->setNodeFilter($evens);

        $actualResult = $this->getNodeIds();
        /**
         * ordered search so not canonicalize the results
         */
        self::assertEquals($expectedResult, $actualResult);
    }
}
