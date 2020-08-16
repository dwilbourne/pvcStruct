<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree;

use pvc\struct\lists\ListOrdered;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\tree\TreeOrdered;
use tests\struct\tree\fixture\TreeTestFixture;

/**
 * Class TreeTestUnordered
 */
class TreeOrderedTest extends TreeTest
{
    public function setUp(): void
    {
        $this->treeid = 3;
        $this->tree = new TreeOrdered();
        $this->tree->setTreeId($this->treeid);
        $this->fixture = new TreeTestFixture($this, $this->treeid);
    }

    public function makeNode(int $nodeid) : TreenodeOrdered
    {
        $list = new ListOrdered();
        return new TreenodeOrdered($nodeid, $list);
    }

    /**
     * @function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne
     * @return int[]
     */
    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne() : array
    {
        return $this->fixture->makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered();
    }

    /**
     * @function makeDepthFirstArrayOfAllNodeIds
     * @return int[]
     */
    public function makeDepthFirstArrayOfAllNodeIds() : array
    {
        return $this->fixture->makeOrderedDepthFirstArrayOfAllNodeIds();
    }

    /**
     * @function makeDepthFirstArrayOfBranchAtNodeid2
     * @return TreenodeOrdered[]
     */
    public function makeDepthFirstArrayOfBranchAtNodeid2() : array
    {
        return $this->fixture->makeOrderedDepthFirstArrayOfBranchAtNodeid2();
    }

    /**
     * @function makeBreadthFirstArrayOfAllNodeIds
     * @return TreenodeOrdered[]
     */
    public function makeBreadthFirstArrayOfAllNodeIds() : array
    {
        return $this->fixture->makeOrderedBreadthFirstArrayOfAllNodeIds();
    }

    /**
     * @function makeBreadthFirstArrayStartingAtNodeid1
     * @return TreenodeOrdered[]
     */
    public function makeBreadthFirstArrayStartingAtNodeid1() : array
    {
        return $this->fixture->makeOrderedBreadthFirstArrayStartingAtNodeid1();
    }

    /**
     * @function makeBreadthFirstArrayTwoLevelsStartingAtRoot
     * @return TreenodeOrdered[]
     */
    public function makeBreadthFirstArrayTwoLevelsStartingAtRoot() : array
    {
        return $this->fixture->makeOrderedBreadthFirstArrayTwoLevelsStartingAtRoot();
    }

    public function testAddNodeWithHydrationIndexSet() : void
    {
        $this->tree->hydrateNodes($this->fixture->makeTreeWithGoodData());
        // children of nodeid = 5 are (in order) 12, 11, 10, 9
        $node = $this->makeNode(22);
        $node->setTreeId($this->tree->getTreeId());
        $node->setParentId(5);
        $node->setHydrationIndex(2);
        $this->tree->addNode($node);
        self::assertEquals(2, $node->getIndex());
    }
}
