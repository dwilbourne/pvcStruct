<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\tree;

use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\tree\Tree;

/**
 * unit tests for unordered trees using mock nodes.
 *
 * The methods tested here do not include those that are already tested in TreeTraitTest (e.g. the public methods
 * common to both ordered and unordered trees).
 *
 * Class TreeTest
 */
class TreeTest extends AbstractTreeTest
{
    public function setUp(): void
    {
		parent::setUp();
        $this->tree = new Tree($this->fixture->getTreeId());
    }

	/**
	 * set the integer ids in the node structure.  Ordered nodes require the additional step of stubbing out the
	 * methods to access the object references (handled in TreeOrderedTest).
	 *
	 * @function makeNode
	 * @param array<string, integer> $row
	 * @return mixed
	 */
	public function makeNode(array $row, bool $isRoot = false, bool $equalsMethod = false)
	{
		$node = $this->makeNodeSkeleton($row);
		$node->method('isRoot')->willReturn($isRoot);
		$node->method('equals')->willReturn($equalsMethod);

		return $node;
	}

	public function makeNodeSkeleton(array $row)
	{
		$node = $this->createStub(Treenode::class);
		$node->method('getNodeId')->willReturn($row['nodeid']);
		$node->method('getParentId')->willReturn($row['parentid']);
		$node->method('getTreeId')->willReturn($row['treeid']);
		return $node;
	}

	/**
	 * testConstruct
	 * @covers \pvc\struct\tree\tree\Tree::__construct
	 */
	public function testConstruct() : void
	{
		$treeid = 3;
		$tree = new Tree($treeid);
		self::assertEquals($treeid, $tree->getTreeId());
	}

	/**
	 * testDeleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\Tree::deleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\Tree::deleteNode
	 */
	public function testDeleteNodeRecurse() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false, true);

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		$deleteBranch = true;
		$this->tree->deleteNode($node_1, $deleteBranch);
		/**
		 * demonstrate $node_1 and its child are both gone
		 */
		self::assertTrue($this->tree->isEmpty());
	}

	/**
	 * testGetChildrenOf
	 * @covers \pvc\struct\tree\tree\Tree::getChildrenOf
	 */
	public function testGetChildrenOf() : void
	{
		/**
		 * need to change the equals expectations on each node so that when the tree calls getChildren with leaf as
		 * its argument, the tree knows that leaf is in the tree.
		 */
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);

		$parentNodeId = 1;
		$parent = $this->tree->getNode($parentNodeId);
		$expectedChildNodeIds = $this->makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne();

		foreach($this->tree->getChildrenOf($parent) as $nodeId => $node) {
			$actualChildNodeIds[] = $nodeId;
		}
		self::assertEqualsCanonicalizing($expectedChildNodeIds, $actualChildNodeIds);
	}

	/**
	 * testGetParentOf
	 * @covers \pvc\struct\tree\tree\Tree::getParentOf
	 */
	public function testGetParentOf() : void
	{
		/**
		 * need to change the equals expectations on each node so that when the tree calls getParentOf with child as
		 * its argument, the tree knows that child is in the tree.
		 */
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		/** node with id = 4 is a child of node with id = 1. */
		$parentId = 1;
		$parentNode = $this->tree->getNode($parentId);
		$childId = 4;
		$child = $this->tree->getNode($childId);
		self::assertSame($parentNode, $this->tree->getParentOf($child));
	}

	/**
	 * This method is called from the trait and in TreeOrderedTest this method actually does something.
	 * setFixtureForTreeSearches
	 */
	protected function setFixtureForTreeSearches() {}

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
