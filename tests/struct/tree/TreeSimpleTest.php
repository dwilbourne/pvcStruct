<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree;

use pvc\struct\tree\iface\node\TreenodeInterface;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\tree\Tree;
use pvc\validator\numeric\ValidatorIntegerNonNegative;
use tests\struct\tree\fixture\TreeTestFixture;

/**
 * Class TreeTestSimple
 */
class TreeSimpleTest extends TreeTest
{
    public function setUp(): void
    {
        $this->treeid = 5;
        $this->tree = new Tree();
        $this->tree->setTreeId($this->treeid);
        $this->fixture = new TreeTestFixture($this, $this->treeid);
    }

    public function makeNode(int $nodeid): TreenodeInterface
    {
        return new Treenode($nodeid);
    }

    /**
     * @function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne
     * @return int[]
     */
    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne() : array
    {
        return $this->fixture->makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered();
    }

    /**
     * @function makeDepthFirstArrayOfAllNodeIds
     * @return int[]
     */
    public function makeDepthFirstArrayOfAllNodeIds() : array
    {
        return $this->fixture->makeUnorderedDepthFirstArrayOfAllNodeIds();
    }

    /**
     * @function makeDepthFirstArrayOfBranchAtNodeid2
     * @return Treenode[]
     */
    public function makeDepthFirstArrayOfBranchAtNodeid2() : array
    {
        return $this->fixture->makeUnorderedDepthFirstArrayOfBranchAtNodeid2();
    }

    /**
     * @function makeBreadthFirstArrayOfAllNodeIds
     * @return Treenode[]
     */
    public function makeBreadthFirstArrayOfAllNodeIds() : array
    {
        return $this->fixture->makeUnorderedBreadthFirstArrayOfAllNodeIds();
    }

    /**
     * @function makeBreadthFirstArrayStartingAtNodeid1
     * @return Treenode[]
     */
    public function makeBreadthFirstArrayStartingAtNodeid1() : array
    {
        return $this->fixture->makeUnorderedBreadthFirstArrayStartingAtNodeid1();
    }

    /**
     * @function makeBreadthFirstArrayTwoLevelsStartingAtRoot
     * @return Treenode[]
     */
    public function makeBreadthFirstArrayTwoLevelsStartingAtRoot() : array
    {
        return $this->fixture->makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot();
    }
}
