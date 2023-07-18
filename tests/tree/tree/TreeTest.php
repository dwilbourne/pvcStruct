<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\tree\tree;


use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\tree\Tree;

/**
 * Class TreeTest
 */
class TreeTest extends TreeAbstractTest
{

    public function setUp(): void
    {
		parent::setUp();
        $this->tree = new Tree($this->treeId);
    }

	/**
	 * makeNodeStub
	 * @return mixed|\PHPUnit\Framework\MockObject\Stub|Treenode|Treenode&\PHPUnit\Framework\MockObject\Stub
	 */
	public function makeNodeStub()
	{
		return $this->createStub(Treenode::class);
	}

	/**
	 * testDeleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNode
	 */
	public function testDeleteNodeRecurse() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());

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
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getChildrenOf
	 */
	public function testGetChildrenOf() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getParentOf
	 */
	public function testGetParentOf() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
