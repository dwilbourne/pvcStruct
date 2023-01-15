<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\tree;

use pvc\interfaces\struct\lists\ordered\ListOrderedInterface;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * Class TreeOrderedTest
 */
class TreeOrderedTest extends AbstractTreeTest
{
	/**
	 * setUp
	 * @throws \Exception
	 */
    public function setUp(): void
    {
	    parent::setUp();
	    $this->tree = new TreeOrdered($this->fixture->getTreeId());
    }

	/**
	 * testConstruct
	 * @covers \pvc\struct\tree\tree\TreeOrdered::__construct
	 */
	public function testConstruct() : void
	{
		$treeid = 3;
		$tree = new TreeOrdered($treeid);
		self::assertEquals($treeid, $tree->getTreeId());
	}

	/**
	 * makeNode
	 * @param array $row
	 * @return mixed|\PHPUnit\Framework\MockObject\Stub|TreenodeOrdered|TreenodeOrdered&\PHPUnit\Framework\MockObject\Stub
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
		$node = $this->createStub(TreenodeOrdered::class);
		$node->method('getNodeId')->willReturn($row['nodeid']);
		$node->method('getParentId')->willReturn($row['parentid']);
		$node->method('getTreeId')->willReturn($row['treeid']);
		$node->method('getIndex')->willReturn($row['index']);
		return $node;
	}

	/**
	 * testAddNodeCallsSetReferences
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 */
	public function testAddNodeCallsSetReferences() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		/**
		 * verify the addNode calls setReferences (unit tested separately) once and properly when adding a node
		 */
		$node->expects($this->once())->method('setReferences')->with($this->tree);
		$this->tree->addNode($node);
	}

	/**
	 * testAddNodeMakesCallToPutNodeIntoChildListOfParent
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 */
	public function testAddNodeMakesCallToPutNodeIntoChildListOfParent() : void
	{
		$parent = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($parent);

		$child = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$child->expects($this->once())->method('getParent')->willReturn($parent);

		$mockChildList = $this->createMock(ListOrderedInterface::class);
		$parent->expects($this->once())->method('getChildren')->willReturn($mockChildList);
		$mockChildList->expects($this->once())->method('add')->with($child->getIndex(), $child);

		$this->tree->addNode($child);
	}

	/**
	 * testDeleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNodeRecurse
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
	public function testDeleteNodeRecurse() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false, true);

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		$node_1->method('getChildrenArray')->willReturn([$node_2]);
		$node_2->method('getChildrenArray')->willReturn([]);

		$deleteBranch = true;
		$this->tree->deleteNode($node_1, $deleteBranch);
		/**
		 * demonstrate $node_1 and its child are both gone
		 */
		self::assertTrue($this->tree->isEmpty());
	}

	/**
	 * testDeleteNodeCleanupCallsForRemovalOfNodeFromParentListOfChildren
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
	public function testDeleteNodeCleanupCallsForRemovalOfNodeFromParentListOfChildren() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false, true);

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		$node_1->method('getChildrenArray')->willReturn([$node_2]);
		$node_2->method('getChildrenArray')->willReturn([]);
		$node_2->method('getParent')->willReturn($node_1);

		$mockChildList = $this->createMock(ListOrderedInterface::class);
		$mockChildList->expects($this->once())->method('delete')->with($node_2->getIndex());
		$node_1->method('getChildren')->willReturn($mockChildList);

		$this->tree->deleteNode($node_2);
	}

	/**
	 * testSetNodesMakesCallsToPopulateChildListsCorrectly
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 */
	public function testSetNodesAddsCorrectNumberOfChildrenInTotal() : void
	{
		/**
		 * there are 13 nodes in the sample tree, so there are 12 children in total.  If we put the same list object
		 * into all thirteen nodes, the list should call its add method 12 times.
		 */

		$nodeArray = $this->makeFullTreeNodeArray(true);
		$mockList = $this->createMock(ListOrderedInterface::class);
		foreach($nodeArray as $node) {
			$node->method('getChildren')->willReturn($mockList);
			$isRootCallback = function() use ($node) {
				/**
				 * nodeid 0 is the root node in the sample tree
				 */
				return (0 == $node->getNodeId());
			};
			$node->method('isRoot')->willReturnCallback($isRootCallback);
		}
		$mockList->expects($this->exactly(12))->method('add');
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * There is no test to make sure that all the children are added in the correct order because that test would be
	 * identical to testing the breadth-first (or depth-first) tree searches.  It's easier to visualize with a
	 * breadth-first search: child nodes are returned in a specific order corresponding to their indices as children of
	 * each parent. So if the breadth-first tree tests pass, then setNodes is working correctly as well.
	 */

	/**
	 * testGetChildrenOfCallsGetElementsOfChildList
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getChildrenOf
	 */
	public function testGetChildrenOfCallsGetElementsOfChildList() : void
	{

		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$this->tree->addNode($node);

		/** does not matter what these child ids are */
		$expectedChildNodeIds = [4, 7, 11];

		$mockChildList = $this->createMock(ListOrderedInterface::class);
		$mockChildList->expects($this->once())->method('getElements')->willReturn($expectedChildNodeIds);

		$node->expects($this->once())->method('getChildren')->willReturn($mockChildList);
		$children = $this->tree->getChildrenOf($node);
	}

	/**
	 * testGetParentOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getParentOf
	 */
	public function testGetParentOfCallsNodesGetParentMethod() : void
	{
		/**
		 * need to change the equals expectations on each node so that when the tree calls getParentOf with child as
		 * its argument, the tree knows that child is in the tree.
		 */
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		/**
		 * node with id = 4 is a child of node with id = 1.
		 */
		$parentId = 1;
		$parentNode = $this->tree->getNode($parentId);
		$childId = 4;
		$child = $this->tree->getNode($childId);
		$child->expects($this->once())->method('getParent')->willReturn($parentNode);
		$someVariable = $this->tree->getParentOf($child);
	}

	/**
	 * The order of the nodes in the fixture is scrambled on purpose so in creating the return arrays, the nodes
	 * appear out of order because order reflects their node indices, not their nodeids.
	 *
	 * setFixtureForTreeSearches
	 */
	protected function setFixtureForTreeSearches() : void
	{
		$childNodes = [$this->tree->getNode(2), $this->tree->getNode(1)];
		$mockChildrenOf0 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf0->method('getElements')->willReturn($childNodes);
		$this->tree->getNode(0)->method('getChildren')->willReturn($mockChildrenOf0);

		$childNodes = [$this->tree->getNode(5), $this->tree->getNode(3), $this->tree->getNode(4)];
		$mockChildrenOf1 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf1->method('getElements')->willReturn($childNodes);
		$this->tree->getNode(1)->method('getChildren')->willReturn($mockChildrenOf1);

		$childNodes = [$this->tree->getNode(6), $this->tree->getNode(7)];
		$mockChildrenOf2 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf2->method('getElements')->willReturn($childNodes);
		$this->tree->getNode(2)->method('getChildren')->willReturn($mockChildrenOf2);

		$childNodes = [$this->tree->getNode(8)];
		$mockChildrenOf3 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf3->method('getElements')->willReturn($childNodes);
		$this->tree->getNode(3)->method('getChildren')->willReturn($mockChildrenOf3);

		$mockChildrenOf4 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf4->method('getElements')->willReturn([]);
		$this->tree->getNode(4)->method('getChildren')->willReturn($mockChildrenOf4);

		$childNodes = [$this->tree->getNode(12), $this->tree->getNode(11), $this->tree->getNode(10),
			$this->tree->getNode(9)];
		$mockChildrenOf5 = $this->createMock(ListOrderedInterface::class);
		$mockChildrenOf5->method('getElements')->willReturn($childNodes);
		$this->tree->getNode(5)->method('getChildren')->willReturn($mockChildrenOf5);
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
}
