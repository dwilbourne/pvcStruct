<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\tree;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\BadTreesearchLevelsException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeArrayException;
use pvc\struct\tree\err\InvalidNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeHasInvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\RootCountForTreeException;
use pvc\struct\tree\err\SetNodesException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * Class AbstractTreeTest
 * @template TreeType
 */
abstract class AbstractTreeTest extends TestCase
{
	/**
	 * @var TreeType
	 */
	protected $tree;

	/**
	 * @var TreenodeConfigurationsFixture
	 */
	protected TreenodeConfigurationsFixture $fixture;

	/**
	 * Sets the fixture - the tree is initialized in the subclasses.
	 *
	 * setUp
	 */
	public function setUp() : void
	{
		$this->fixture = new TreenodeConfigurationsFixture();
	}

	/**
	 * makes a node.  It is abstract Tree will make a Treenode and TreeOrdered will make TreenodeOrdered.  The method
	 * return is left untyped to emphasize there are varying return types.  Because TreenodeOrdered extends Treenode,
	 * it would not be technically wrong for the abstract method to have a return type of Treenode, but it would be
	 * misleading.  The $isRoot and $equalsMethod parameters control method expectations for the node.
	 *
	 * makeNode
	 * @param array $nodeData
	 * @return mixed
	 */
    abstract protected function makeNode(array $nodeData, bool $isRoot = false, bool $equalsMethod = false);
	abstract protected function makeNodeSkeleton(array $nodeData);

	public function makeFullTreeNodeArray(bool $equalsReturnValue = false) : array
	{
		$nodeData = $this->fixture->makeArrayOfNodeIdsForTree();
		$nodeArray = [];
		foreach($nodeData as $row) {
			$nodeId = $row['nodeid'];
			$isRoot = ($nodeId === 0);
			$node = $this->makeNode($row, $isRoot, $equalsReturnValue);
			$nodeArray[$nodeId] = $node;
		}
		return $nodeArray;
	}

    abstract protected function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne(): array;
    abstract protected function makeDepthFirstArrayOfAllNodeIds(): array;
    abstract protected function makeDepthFirstArrayOfBranchAtNodeid2(): array;
    abstract protected function makeBreadthFirstArrayOfAllNodeIds(): array;
    abstract protected function makeBreadthFirstArrayStartingAtNodeid1(): array;
    abstract protected function makeBreadthFirstArrayTwoLevelsStartingAtRoot(): array;

	/**
	 * These tests are ordered so that we first establish that we can get nodes into the tree and the tree is valid.
	 * So the addNode and setNodes methods are tested first.
	 *
	 * Once we have established we can get nodes into the tree, then we can rely on those methods to populate the
	 * tree for further testing.
	 */

	/**
	 * before getting into the addNode and setNodes tests, do the setter / getter testing for treeid property.
	 */

	/**
	 * testGetSetTreeid
	 * @covers \pvc\struct\tree\tree\TreeTrait::setTreeId
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeId
	 */
	public function testGetSetTreeid(): void
	{
		self::assertEquals($this->fixture->getTreeId(), $this->tree->getTreeId());
	}

	/**
	 * testSetInvalidTreeidThrowsException
	 * @throws \Exception
	 * @covers \pvc\struct\tree\tree\TreeTrait::setTreeId
	 * @covers \pvc\struct\tree\tree\TreeTrait::validateTreeId
	 */
	public function testSetInvalidTreeidThrowsException() : void
	{
		self::expectException(InvalidTreeidException::class);
		/**
		 * treeids must be integers >= 0
		 */
		$this->tree->setTreeId(-2);
	}

	/**
	 * testSetTreeIdWorksWhenTheTreeHasNodes
	 * @covers \pvc\struct\tree\tree\TreeTrait::setTreeId
	 */
	public function testSetTreeIdWorksWhenTreeIsEmpty() : void
	{
		$newTreeId = 5;
		$this->tree->setTreeId($newTreeId);
		self::assertEquals($newTreeId, $this->tree->getTreeId());
	}

	/**
	 * testAddNodeRoot
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\Tree::getRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::setRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setRoot
	 */
	public function testAddNodeRoot() : void
	{
		/**
		 * construct node with null parentid, add it to the tree and the root is then set
		 */
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($node);
		self::assertEquals($node, $this->tree->getRoot());
	}

	/**
	 * testSetTreeIdFailsWhenTreeIsNotEmpty
	 * @covers \pvc\struct\tree\tree\TreeTrait::setTreeId
	 *
	 */
	public function testSetTreeIdFailsWhenTreeIsNotEmpty() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($node);
		$this->expectException(SetTreeIdException::class);
		$this->tree->setTreeId($node->getNodeId() + 1);
	}

	/**
	 * testAddNodeThrowsExceptionWhenNodeIdIsNotSet
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 */
	public function testAddNodeThrowsExceptionWhenNodeIdIsNotSet() : void
	{
		$node = $this->makeNode($this->fixture->makeNodeRowWithNullNodeId());
		$node->method('getNodeId')->willReturn(null);
		$this->expectException(InvalidNodeIdException::class);
		$this->tree->addNode($node);
	}

	/**
	 * testAddNodeThrowsExceptionWhenNodeAlreadyInTree
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 */
	public function testAddNodeThrowsExceptionWhenNodeAlreadyInTree(): void
	{
		/**
		 * when addNode is called the second time, the first node's equals method will return true, indicating that
		 * the second node is already in the tree.
		 */
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$this->tree->addNode($node_1);

		$node_2 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->expectException(AlreadySetNodeidException::class);
		/**
		 * try to add the node, the first node says it equals this new node, and we get an exception
		 */
		$this->tree->addNode($node_2);
	}

	/**
	 * testAddNodeThrowsExceptionNodeHasWrongTreeId
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 */
	public function testAddNodeThrowsExceptionNodeHasWrongTreeId(): void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithBadTreeId());
		self::expectException(NodeHasInvalidTreeidException::class);
		$this->tree->addNode($node);
	}

	/**
	 * testAddNodeThrowsExceptionParentDoesNotExistInTree
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 */
	public function testAddNodeThrowsExceptionParentDoesNotExistInTree(): void
	{
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$this->expectException(InvalidParentNodeException::class);
		$this->tree->addNode($node);
	}

	/**
	 * testAddNodeSetsRootAndAddsNodeToNodelistWhenNodeHasNullParentId
	 *
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\Tree::getRoot
	 *
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getRoot
	 *
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeTrait::nodeCount
	 */
	public function testAddNodeSetsRootAndAddsNodeToNodesArrayWhenNodeHasNullParentId() : void
	{
		/**
		 * demonstrate root is null at creation of tree and then addNode sets root reference as well as adding node
		 * to the nodes array.
		 */
		self::assertNull($this->tree->getRoot());
		$root = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($root);
		self::assertSame($root, $this->tree->getRoot());
		self::assertEquals(1, $this->tree->nodeCount());
	}

	/**
	 * testAddNodeThrowsAlreadySetRootExceptionWhenAddingRootTwice
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\Tree::setRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setRoot
	 * @covers \pvc\struct\tree\tree\Tree::addNodeToNodelist
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodeToNodelist
	 */
	public function testAddNodeThrowsAlreadySetRootExceptionWhenAddingRootTwice(): void
	{
		$root = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($root);

		$secondRoot = $this->makeNode($this->fixture->makeSecondRootNodeRowWithDifferentNodeId(), true);
		self::expectException(AlreadySetRootException::class);
		$this->tree->addNode($secondRoot);
	}

	/**
	 * next let's test setNodes, so we can get nodes into the tree in bulk
	 */

	/**
	 * testSetNodesThrowsExceptionIfCalledWhenTreeIsNotEmpty
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionIfCalledWhenTreeIsNotEmpty() : void
	{
		$root = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($root);

		$nodeArray = $this->makeFullTreeNodeArray();
		$this->expectException(SetNodesException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenArgumentContainsElementThatDoesNotImplementTreenodeInterface
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenArgumentContainsElementThatDoesNotImplementTreenodeInterface() : void
	{
		$nodeList = [new \stdClass()];
		$this->expectException(InvalidNodeException::class);
		$this->tree->setNodes($nodeList);
	}

	/**
	 * testSetNodesThrowsExceptionWhenNodeHasWrongTreeId
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeHasWrongTreeId() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithBadTreeId(), true);
		$nodeArray = [$node->getNodeId() => $node];
		$this->expectException(NodeHasInvalidTreeidException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenNodeArrayKeyDoesNotMatchNotId
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeArrayKeyDoesNotMatchNotId() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$nodeArray = [$node->getNodeId() + 1 => $node];
		$this->expectException(InvalidNodeArrayException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenNodeArrayHasMultipleRoots
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeArrayHasMultipleRoots() : void
	{
		/**
		 * nodeids 0, 6, 7 all have null parent ids
		 */
		$rootNodeIds = [0, 6, 7];
		$nodeData = $this->fixture->makeTreeWithMultipleRoots();

		$nodeArray = [];
		foreach($nodeData as $row) {
			$index = $row['nodeid'];
			$isRoot = in_array($index, $rootNodeIds);
			$nodeArray[$index] = $this->makeNode($row, $isRoot);
		}

		self::expectException(RootCountForTreeException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * one or more nodes in the node data have parentids that do not exist in the tree
	 *
	 * testSetNodesThrowsExceptionWithBadParentData
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWithBadParentData(): void
	{
		$nodeData = $this->fixture->makeTreeWithNonExistentParentData();
		$nodeArray = [];
		foreach($nodeData as $row) {
			$index = $row['nodeid'];
			$isRoot = ($index === 0);
			$nodeArray[$index] = $this->makeNode($row, $isRoot);
		}
		self::expectException(InvalidParentNodeException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWithCircularGraphData
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::checkCircularity
	 * @covers \pvc\struct\tree\tree\TreeOrdered::checkCircularity
	 */
	public function testSetNodesThrowsExceptionWithCircularGraphData(): void
	{
		$nodeData = $this->fixture->makeTreeWithCircularParents();
		$nodeArray = [];
		foreach($nodeData as $row) {
			$index = $row['nodeid'];
			$isRoot = ($index === 0);
			$nodeArray[$index] = $this->makeNode($row, $isRoot);
		}
		self::expectException(CircularGraphException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesSucceedsWithEmptyArrayAsArgument
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesSucceedsWithEmptyArrayAsArgument() : void
	{
		$this->tree->setNodes([]);
		self::assertEmpty($this->tree->getNodes());
	}

	/**
	 * insures that all the nodes in $nodeArray make it into the nodes array in the tree
	 *
	 * testSetNodesAddsCorrectNumberOfNodesToNodesArray
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeTrait::nodeCount
	 * @covers \pvc\struct\tree\tree\Tree::checkCircularity
	 * @covers \pvc\struct\tree\tree\TreeOrdered::checkCircularity
	 * @covers \pvc\struct\tree\tree\Tree::addNodesToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesAddsCorrectNumberOfNodesToNodesArray() : void
	{
		$nodeArray = $this->makeFullTreeNodeArray();
		$this->tree->setNodes($nodeArray);
		self::assertEquals(count($nodeArray), $this->tree->nodeCount());
	}

	/**
	 * Now that we have established we can get nodes into the tree and the tree is valid, we can work our way through
	 * the remaining methods.  These next methods being tested are located in TreeTrait.
	 */

	/**
	 * testGetNodes
	 * @covers \pvc\struct\tree\tree\Tree::getNodes()
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getNodes()
	 */
	public function testGetNodes() : void
	{
		self::assertEmpty($this->tree->getNodes());

		$nodeArray = $this->makeFullTreeNodeArray();
		$this->tree->setNodes($nodeArray);
		self::assertEqualsCanonicalizing($nodeArray, $this->tree->getNodes());
	}

	/**
	 * hasNode relies on the equals method of the node object to do its comparisons and that method is unit tested
	 * elsewhere.  All we need to test for hasNode is that it visits each node in the tree until equals returns true.
	 *  Unfortunately, coding this test requires using a callback for the equals method on the nodes.  This is the 
	 * only test for which that is true, and changing the makeNode method to accept a callback presents other even 
	 * more complicated problems.  So (*sigh*), have to code up the mock from scratch.
	 *
	 * testHasNode
	 * @covers \pvc\struct\tree\tree\TreeTrait::hasNode
	 */
	public function testHasNodeVisitsNodesUntilItFindsAMatch() : void
	{
		$nodeData = $this->fixture->makeArrayOfNodeIdsForTree();
		$nodeArray = [];
		$actualVisits = 0;
		
		foreach($nodeData as $row) {
			$index = $row['nodeid'];
			$node = $this->makeNodeSkeleton($row);
			
			$isRoot = ($index === 0);
			$node->method('isRoot')->willReturn($isRoot);
			
			/**
			 * $node->equals will return true when it hits nodeid == 3, which is the 6th node in the fixture
			 */
			$equalsMethodCallback = function() use ($index, &$actualVisits) {
				$actualVisits++;
				return ($index == 3);
			};
			$node->method('equals')->willReturnCallback($equalsMethodCallback);
			$nodeArray[$index] = $node;
		}
		$this->tree->setNodes($nodeArray);

		$nodeToBeTested = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$expectedVisits = 6;
		
		$this->tree->hasNode($nodeToBeTested);
		self::assertEquals($expectedVisits, $actualVisits);
	}

	/**
	 * testHasNodeReturnsFalseIfItDoesNotFindAMatch
	 * @covers \pvc\struct\tree\tree\TreeTrait::hasNode
	 */
	public function testHasNodeReturnsFalseIfItDoesNotFindAMatch() : void
	{
		/** the equals method for all nodes in the tree defaults to false */
		$nodeArray = $this->makeFullTreeNodeArray();
		$nodeData = $this->fixture->makeSingleNodeRowWithRootAsParent();
		$node = $this->makeNode($nodeData);
		self::assertFalse($this->tree->hasNode($node));
	}

	/**
	 * testIsEmpty
	 * @covers \pvc\struct\tree\tree\TreeTrait::isEmpty
	 */
	public function testIsEmpty() : void
	{
		self::assertTrue($this->tree->isEmpty());
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($node_1);
		self::assertFalse($this->tree->isEmpty());
	}

	/**
	 * testNodeCount
	 * @covers \pvc\struct\tree\tree\TreeTrait::nodeCount
	 */
	public function testNodeCount() : void
	{
		self::assertEquals(0, $this->tree->nodeCount());

		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($node_1);
		self::assertEquals(1, $this->tree->nodeCount());

		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false);
		$this->tree->addNode($node_2);
		self::assertEquals(2, $this->tree->nodeCount());
	}

	/**
	 * testDeleteNodeRoot
	 * @covers \pvc\struct\tree\tree\TreeTrait::verifyDeleteNodeInitialConditions
	 * @covers \pvc\struct\tree\tree\Tree::deleteNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
    public function testDeleteNodeThrowsExceptionWhenNodeIsNotInTree(): void
    {
	    /**
	     * deleteNode calls hasNode, which uses this method to ensure that the node we are trying to delete is
	     * actually in the tree. So the third (equalsMethod) parameter to makeNode must return false so that hasNode
	     * returns false..
	     */
	    $node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, false);
	    $this->tree->addNode($node);
		$this->expectException(NodeNotInTreeException::class);
        $this->tree->deleteNode($node);
    }

	/**
	 * testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse
	 * @covers \pvc\struct\tree\tree\TreeTrait::verifyDeleteNodeInitialConditions
	 * @covers \pvc\struct\tree\tree\Tree::deleteNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
	public function testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true, true);
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false, true);

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		$deleteBranch = false;
		$this->expectException(DeleteInteriorNodeException::class);
		$this->tree->deleteNode($node_1, $deleteBranch);
	}

	/**
	 * testing the deleteNodeRecurse trait is done individually in TreeTest and TreeOrderedTest because TreeOrdered
	 * has nodes where we have to stub for the getChildren method.
	 */

	/**
	 * the getTreeDepthFirst and getTreeBreadthFirst tests are kept in TreeTest and TreeOrderedTest because the
	 * implementations of getting children are different between the two and so mocks and expectations have to be set
	 * up differently for each.
	 */


	/**
	 * now test the methods from the tree classes that are not in TreeTrait
	 */

	/**
	 * testGetRoot
	 * @covers \pvc\struct\tree\tree\Tree::getRoot
 	 * @covers \pvc\struct\tree\tree\TreeOrdered::getRoot
	 */
	public function testGetRoot() : void
	{
		self::assertNull($this->tree->getRoot());

		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($node_1);

		self::assertSame($node_1, $this->tree->getRoot());
	}

	/**
	 * testGetNode
	 * @covers \pvc\struct\tree\tree\Tree::getNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getNode
	 */
	public function testGetNode() : void
	{
		$nonExistentNodeid = 8;
		self::assertNull($this->tree->getNode($nonExistentNodeid));

		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false);

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		self::assertEquals($node_1, $this->tree->getNode($node_1->getNodeId()));
		self::assertEquals($node_2, $this->tree->getNode($node_2->getNodeId()));
	}

	/**
	 * setNodes has already been tested at the top of this class so that we can get nodes into the tree in bulk
	 */

	/**
	 * testGetChildrenThrowsExceptionWhenArgIsNotInTree
	 * @covers \pvc\struct\tree\tree\Tree::getChildrenOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getChildrenOf
	 */
    public function testGetChildrenThrowsExceptionWhenArgIsNotInTree(): void
    {
	    $node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$this->expectException(NodeNotInTreeException::class);
		$this->tree->getChildrenOf($node_2);
    }

	/**
	 * testGetChildrenOfReturnsEmptyWithLeafArgument
	 * @covers \pvc\struct\tree\tree\Tree::getChildrenOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getChildrenOf
	 */
    public function testGetChildrenOfReturnsEmptyWithLeafArgument(): void
    {
	    /**
	     * need to change the equals expectations on each node so that when the tree calls getChildren with leaf as
	     * its argument, the tree knows that leaf is in the tree.
	     */
	    $nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);

		$leafNodeId = 12;
        $leaf = $this->tree->getNode($leafNodeId);
        self::assertEmpty($this->tree->getChildrenOf($leaf));
    }

	/**
	 * tests for getChildrenOf are done separately because the mock setup between ordered and unordered is different.
	 *
	 * testGetChildrenOfThrowsExceptionWhenNodeIsNotInTree
	 * @covers \pvc\struct\tree\tree\Tree::getChildrenOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getChildrenOf
	 */
	public function testGetChildrenOfThrowsExceptionWhenNodeIsNotInTree() : void
	{
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), true);
		/**
		 * node has not been added to the tree
		 */
		$this->expectException(NodeNotInTreeException::class);
		$children = $this->tree->getChildrenOf($node);
	}

	/**
	 * testGetParentOfThrowsExceptionIfNodeNotInTree
	 * @covers \pvc\struct\tree\tree\Tree::getParentOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getParentOf
	 */
    public function testGetParentOfThrowsExceptionIfNodeNotInTree(): void
    {
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), true);
	    /**
	     * node has not been added to the tree
	     */
        $this->expectException(NodeNotInTreeException::class);
		$parent = $this->tree->getParentOf($node);
    }

	/**
	 * testHasLeafReturnsFalseOnLeafThatIsNotinTree
	 * @covers \pvc\struct\tree\tree\Tree::hasLeafWithId
	 * @covers \pvc\struct\tree\tree\TreeOrdered::hasLeafWithId
	 */
	public function testHasLeafWithIdReturnsFalseOnLeafThatIsNotinTree(): void
	{
		$nonExistentNodeId = 99;
		self::assertFalse($this->tree->hasLeafWithId($nonExistentNodeId));
	}

	/**
	 * testHasLeafWithId
	 * @covers \pvc\struct\tree\tree\Tree::hasLeafWithId
	 * @covers \pvc\struct\tree\tree\TreeOrdered::hasLeafWithId
	 */
	public function testHasLeafWithId(): void
	{
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		self::assertFalse($this->tree->hasLeafWithId(0));
		self::assertFalse($this->tree->hasLeafWithId(5));
		self::assertTrue($this->tree->hasLeafWithId(12));
	}

	/**
	 * testGetLeaves
	 * @covers \pvc\struct\tree\tree\Tree::getLeaves
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getLeaves
	 */
	public function testGetLeaves(): void
	{
		self::assertIsArray($this->tree->getLeaves());
		self::assertEmpty($this->tree->getLeaves());

		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		$expectedResult = $this->fixture->makeArrayOfGoodDataLeafNodeIds();

		$nodeArray = $this->tree->getLeaves();

		$actualResult = [];
		foreach ($nodeArray as $node) {
			$actualResult[] = $node->getNodeId();
		}
		self::assertEqualsCanonicalizing($expectedResult, $actualResult);
	}

	/**
	 * testHasInteriorNodeWithIdReturnsFalseWhenNotInTree
	 * @covers \pvc\struct\tree\tree\Tree::hasInteriorNodeWithId
	 * @covers \pvc\struct\tree\tree\TreeOrdered::hasInteriorNodeWithId
	 */
	public function testHasInteriorNodeWithIdReturnsFalseWhenNotInTree(): void
	{
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent(), false);
		self::assertFalse($this->tree->hasInteriorNodeWithId($node->getNodeId()));
	}

	/**
	 * testHasInteriorNodeWithId
	 * @covers \pvc\struct\tree\tree\Tree::hasInteriorNodeWithId
	 * @covers \pvc\struct\tree\tree\TreeOrdered::hasInteriorNodeWithId
	 */
	public function testHasInteriorNodeWithId(): void
	{
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		self::assertTrue($this->tree->hasInteriorNodeWithId(0));
		self::assertTrue($this->tree->hasInteriorNodeWithId(5));
		self::assertFalse($this->tree->hasInteriorNodeWithId(12));
	}

	/**
	 * testGetInteriorNodes
	 * @covers \pvc\struct\tree\tree\Tree::getInteriorNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getInteriorNodes
	 */
	public function testGetInteriorNodes(): void
	{
		self::assertIsArray($this->tree->getInteriorNodes());
		self::assertEmpty($this->tree->getInteriorNodes());

		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		$expectedResult = $this->fixture->makeArrayOfGoodDataInteriorNodeIds();

		$nodeArray = $this->tree->getInteriorNodes();

		$actualResult = [];
		foreach ($nodeArray as $node) {
			$actualResult[] = $node->getNodeId();
		}
		self::assertEqualsCanonicalizing($expectedResult, $actualResult);
	}

	/**
	 * testVerifyTreeSearchInitialConditionsSetsDefaultStartNodeToRoot
	 * @covers \pvc\struct\tree\tree\TreeTrait::verifyTreeSearchInitialConditions
	 */
	public function testVerifyTreeSearchInitialConditionsSetsDefaultsProperly() : void
	{
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		/**
		 * In TreeOrderedTest, this sets up all the mocking for children
		 * In TreeTest, this is an empty method that does nothing.
		 */
		$this->setFixtureForTreeSearches();

		$searchResult = $this->tree->getTreeDepthFirst();
		/**
		 * verify root node is in the result of the search, so we know that the search started at the root, which is
		 * not supplied in the call to getTreeDepthFirst
		 */
		self::assertTrue(in_array($this->tree->getRoot(), $searchResult));
		/**
		 * verify that the number of nodes returning from the search equals the total number of nodes in the tree to
		 * begin with.  So the callback was defaulted properly because it always returns true
		 */
		self::assertEquals($this->tree->nodeCount(), count($searchResult));
	}

	/**
	 * testVerifyInitialConditionsThrowsExceptionWithStartNodeNotInTree
	 * @covers \pvc\struct\tree\tree\TreeTrait::verifyTreeSearchInitialConditions
	 */
	public function testVerifyInitialConditionsThrowsExceptionWithStartNodeNotInTree() : void
	{
		$nodeArray = $this->makeFullTreeNodeArray(false);
		$this->tree->setNodes($nodeArray);
		$startNode = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$this->expectException(NodeNotInTreeException::class);
		$nodeList = $this->tree->getTreeDepthFirst($startNode);
	}

	/**
	 * tests that the search returns all nodes in the correct order when starting from the root
	 *
	 * testGetTreeDepthFirstFullTree
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirstRecurse
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirstRecurse
	 */
    public function testGetTreeDepthFirstFullTree(): void
    {
	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();

	    $searchResult = $this->tree->getTreeDepthFirst();

	    /**
	     * get the nodeid from each node into an array and reindex the array
	     */
	    $actualResult = array_values(array_map(function ($node) { return $node->getNodeId(); }, $searchResult));

        $expectedResult = array_values($this->makeDepthFirstArrayOfAllNodeIds());
        self::assertEquals($expectedResult, $actualResult);
    }

	/**
	 * tests that the search returns partial tree in the correct order when starting from interior node with nodeid = 2
	 *
	 * testGetTreeDepthFirstFromBranchNode
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirstRecurse
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirstRecurse
	 */
    public function testGetTreeDepthFirstFromBranchNode(): void
    {
	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();


	    $branchNodeId = 2;
        $searchResult = $this->tree->getTreeDepthFirst($this->tree->getNode($branchNodeId));
	    $actualResult = array_values(array_map(function ($node) { return $node->getNodeId(); }, $searchResult));

		$expectedResult = array_values($this->makeDepthFirstArrayOfBranchAtNodeid2());
        self::assertEquals($expectedResult, $actualResult);
    }

	/**
	 * testGetTreeDepthFirstWhereCallbackDoesNotAlwaysReturnTrue
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\Tree::getTreeDepthFirstRecurse
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getTreeDepthFirstRecurse
	 */
    public function testGetTreeDepthFirstWhereCallbackDoesNotAlwaysReturnTrue(): void
    {
	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();

	    $callback = function(TreenodeInterface $node) {
			/**
			 * only return nodes with an even numbered nodeid
			 */
			return ($node->getNodeId() % 2 == 0);
		};
	    $searchResult = $this->tree->getTreeDepthFirst(null, $callback);
	    /**
	     * get the nodeid from each node into an array and reindex the array
	     */
		$actualResults = array_values(array_map(function ($node) { return $node->getNodeId(); }, $searchResult));

		$testCallback = function(int $nodeId) {
			return ($nodeId % 2 == 0);
		};
	    $expectedResults = array_values(array_filter($this->makeDepthFirstArrayOfAllNodeIds(), $testCallback));

		self::assertTrue($actualResults === $expectedResults);
    }

	/**
	 * testGetTreeBreadthFirstFullTree
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirstRecurse
	 */
    public function testGetTreeBreadthFirstFullTree(): void
    {
        $expectedResult = array_values($this->makeBreadthFirstArrayOfAllNodeIds());

	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();

	    $callback = function ($node) { return $node->getNodeId(); };
	    $actualResult = array_values(array_map($callback, $this->tree->getTreeBreadthFirst()));

        self::assertEquals($expectedResult, $actualResult);
    }

	/**
	 * testGetTreeBreadthFirstPartialFromNodeIdOne
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirstRecurse
	 */
    public function testGetTreeBreadthFirstPartialFromNodeIdOne(): void
    {
        $expectedResult = array_values($this->makeBreadthFirstArrayStartingAtNodeid1());

	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();

	    $startNodeId = 1;
		$startNode = $this->tree->getNode($startNodeId);
	    $callback = function ($node) { return $node->getNodeId(); };
	    $actualResult = array_values(array_map($callback, $this->tree->getTreeBreadthFirst($startNode)));

        self::assertEquals($expectedResult, $actualResult);
    }

	/**
	 * testGetTreeBreadthFirstThrowsExceptionWithBadMaxLevelsParameter
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirst
	 */
	public function testGetTreeBreadthFirstThrowsExceptionWithBadMaxLevelsParameter() : void
	{
		$nodeArray = $this->makeFullTreeNodeArray(true);
		$this->tree->setNodes($nodeArray);
		$this->expectException(BadTreesearchLevelsException::class);
		$nodes = $this->tree->getTreeBreadthFirst(null, null, -2);

	}
	/**
	 * testGetTreeBreadthFirstPartialTwoLevelsFromRoot
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeTrait::getTreeBreadthFirstRecurse
	 */
    public function testGetTreeBreadthFirstPartialTwoLevelsFromRoot(): void
    {
        $expectedResult = array_values($this->makeBreadthFirstArrayTwoLevelsStartingAtRoot());

	    $nodeArray = $this->makeFullTreeNodeArray(true);
	    $this->tree->setNodes($nodeArray);

	    /**
	     * In TreeOrderedTest, this sets up all the mocking for children
	     * In TreeTest, this is an empty method that does nothing.
	     */
	    $this->setFixtureForTreeSearches();

	    $startNode = $this->tree->getRoot();
        $levels = 2;
	    $callback = function ($node) { return $node->getNodeId(); };
	    $actualResult = array_values(array_map($callback, $this->tree->getTreeBreadthFirst($startNode, null, $levels)));

        self::assertEquals($expectedResult, $actualResult);
    }

}
