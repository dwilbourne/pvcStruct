<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\tree;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreeAbstract;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * Class AbstractTreeTest
 * @template TreeType
 */
abstract class TreeAbstractTest extends TestCase
{
	/**
	 * @var int
	 */
	protected int $treeId;

	/**
	 * @var Tree | TreeOrdered
	 */
	protected $tree;

	/**
	 * @var MockObject
	 */
	protected $abstractTree;

	/**
	 * @var TreenodeConfigurationsFixture
	 */
	protected TreenodeConfigurationsFixture $fixture;

	/**
	 * Sets the fixture - the abstractTree is initialized in the subclasses.
	 *
	 * setUp
	 */
	public function setUp() : void
	{
		$this->fixture = new TreenodeConfigurationsFixture();
		$this->treeId = $this->fixture->getTreeId();
		$this->abstractTree = $this->getMockBuilder(TreeAbstract::class)
							->setConstructorArgs([$this->treeId])
							->getMockForAbstractClass(TreeAbstract::class);
	}

	/**
	 * stubs a node.  TreeTest will stub a Treenode and TreeOrderedTest will stub TreenodeOrdered.  The method
	 * return is left untyped to emphasize there are varying return types.  Because TreenodeOrdered extends Treenode,
	 * it would not be technically wrong for the abstract method to have a return type of Treenode, but it would be
	 * misleading.
	 *
	 * makeNode
	 * @param array $nodeData
	 * @return mixed
	 */
    abstract protected function makeNodeStub();

	/**
	 * setGeneralNodeExpectations
	 * @param MockObject&\pvc\interfaces\struct\tree\node\TreenodeInterface $node
	 * @param array $row
	 */
	public function setGeneralNodeExpectations(&$node, array $row) : void
	{
		$node->method('getNodeId')->willReturn($row['nodeid']);
		$node->method('getParentId')->willReturn($row['parentid']);
		$node->method('getTreeId')->willReturn($row['treeid']);
		$node->method('isRoot')->willReturn(is_null($node->getParentId()));
	}

	protected function makeNode(array $row)
	{
		$node = $this->makeNodeStub();
		$this->setGeneralNodeExpectations($node, $row);
		return $node;
	}

	public function makeNodeArray(array $nodeData) : array
	{
		foreach($nodeData as $row) {
			$nodeId = $row['nodeid'];
			$node = $this->makeNode($row);
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
	 * These tests are ordered so that we first establish that we can get nodes into the abstractTree and the abstractTree is valid.
	 * So the addNode and setNodes methods are tested first.
	 *
	 * Once we have established we can get nodes into the abstractTree, then we can rely on those methods to populate the
	 * abstractTree for further testing.
	 */

	/**
	 * before getting into the addNode and setNodes tests, do the setter / getter testing for treeid property.
	 */

	/**
	 * testConstruct
	 * @covers \pvc\struct\tree\tree\TreeAbstract::__construct
	 */
	public function testConstruct() : void
	{
		self::assertInstanceOf(MockObject::class, $this->abstractTree);
	}

	/**
	 * testGetSetTreeid
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeId
	 * @covers \pvc\struct\tree\tree\TreeAbstract::validateTreeId
	 */
	public function testGetSetTreeid(): void
	{
		self::assertEquals($this->fixture->getTreeId(), $this->abstractTree->getTreeId());
	}

	/**
	 * testSetInvalidTreeidThrowsException
	 * @throws \Exception
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
	 * @covers \pvc\struct\tree\tree\TreeAbstract::validateTreeId
	 */
	public function testSetInvalidTreeidThrowsException() : void
	{
		self::expectException(InvalidTreeidException::class);
		/**
		 * treeids must be integers >= 0
		 */
		$this->abstractTree->setTreeId(-2);
	}

	/**
	 * testSetTreeIdWorksWhenTheTreeHasNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
	 */
	public function testSetTreeIdWorksWhenTreeIsEmpty() : void
	{
		$newTreeId = 5;
		$this->abstractTree->setTreeId($newTreeId);
		self::assertEquals($newTreeId, $this->abstractTree->getTreeId());
	}

	/**
	 * testAddNodeRoot
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 *
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getRoot
	 *
	 */
	public function testAddNodeRoot() : void
	{
		/**
		 * construct node with null parentid, add it to the abstractTree and the root is then set
		 */
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($node);
		self::assertEquals($node, $this->tree->getRoot());
	}

	/**
	 * testSetTreeIdFailsWhenTreeIsNotEmpty
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
	 *
	 */
	public function testSetTreeIdFailsWhenTreeIsNotEmpty() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($node);
		$this->expectException(SetTreeIdException::class);
		$this->tree->setTreeId($node->getNodeId() + 1);
	}

	/**
	 * testAddNodeThrowsExceptionWhenNodeAlreadyInTree
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
	 */
	public function testAddNodeThrowsExceptionWhenNodeAlreadyInTree(): void
	{
		/**
		 * when addNode is called the second time, the first node's equals method will return true, indicating that
		 * the second node is already in the abstractTree.
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
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
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 *
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::nodeCount
	 */
	public function testAddNodeSetsRootAndAddsNodeToNodesArrayWhenNodeHasNullParentId() : void
	{
		/**
		 * demonstrate root is null at creation of tree and then addNode sets root reference as well as adding node
		 * to the nodes array.
		 */
		self::assertNull($this->tree->getRoot());
		$root = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($root);
		self::assertSame($root, $this->tree->getRoot());
		self::assertEquals(1, $this->tree->nodeCount());
	}

	/**
	 * testAddNodeThrowsAlreadySetRootExceptionWhenAddingRootTwice
	 * @covers \pvc\struct\tree\tree\Tree::addNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::addNode
	 *
	 * @covers \pvc\struct\tree\tree\TreeAbstract::setRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodeToNodelistAndSetRoot
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
	 *
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionIfCalledWhenTreeIsNotEmpty() : void
	{
		$root = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData(), true);
		$this->tree->addNode($root);

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->expectException(SetNodesException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenArgumentContainsElementThatDoesNotImplementTreenodeInterface
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeHasWrongTreeId() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithBadTreeId());
		$nodeArray = [$node->getNodeId() => $node];
		$this->expectException(NodeHasInvalidTreeidException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenNodeArrayKeyDoesNotMatchNotId
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeArrayKeyDoesNotMatchNotId() : void
	{
		$node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$nodeArray = [$node->getNodeId() + 1 => $node];
		$this->expectException(InvalidNodeArrayException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWhenNodeArrayHasMultipleRoots
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWhenNodeArrayHasMultipleRoots() : void
	{
		/**
		 * nodeids 0, 6, 7 all have null parent ids
		 */
		$nodeArray = $this->makeNodeArray($this->fixture->makeTreeWithMultipleRoots());
		self::expectException(RootCountForTreeException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * one or more nodes in the node data have parentids that do not exist in the abstractTree
	 *
	 * testSetNodesThrowsExceptionWithBadParentData
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesThrowsExceptionWithBadParentData(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeTreeWithNonExistentParentData());
		self::expectException(InvalidParentNodeException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesThrowsExceptionWithCircularGraphData
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::checkCircularity
	 */
	public function testSetNodesThrowsExceptionWithCircularGraphData(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeTreeWithCircularParents());
		self::expectException(CircularGraphException::class);
		$this->tree->setNodes($nodeArray);
	}

	/**
	 * testSetNodesSucceedsWithEmptyArrayAsArgument
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesSucceedsWithEmptyArrayAsArgument() : void
	{
		$this->tree->setNodes([]);
		self::assertEmpty($this->abstractTree->getNodes());
	}

	/**
	 * insures that all the nodes in $nodeArray make it into the nodes array in the tree
	 *
	 * testSetNodesAddsCorrectNumberOfNodesToNodesArray
	 * @covers \pvc\struct\tree\tree\Tree::setNodes
	 * @covers \pvc\struct\tree\tree\TreeOrdered::setNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::nodeCount
	 * @covers \pvc\struct\tree\tree\TreeAbstract::checkCircularity
	 * @covers \pvc\struct\tree\tree\TreeAbstract::addNodesToNodelistAndSetRoot
	 */
	public function testSetNodesAddsCorrectNumberOfNodesToNodesArray() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);
		self::assertEquals(count($nodeArray), $this->tree->nodeCount());
	}

	/**
	 * Now that we have established we can get nodes into the abstractTree and the abstractTree is valid, we can work our way through
	 * the remaining methods.  These next methods being tested are located in TreeAbstract.
	 */

	/**
	 * testGetNodes
	 * @covers \pvc\struct\tree\tree\Tree::getNodes()
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getNodes()
	 */
	public function testGetNodes() : void
	{
		self::assertEmpty($this->tree->getNodes());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);
		self::assertEqualsCanonicalizing($nodeArray, $this->tree->getNodes());
	}

	/**
	 * Test that hasNode visits each node in the tree until the test node matches.
	 *
	 * testHasNode
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasNode
	 */
	public function testHasNodeVisitsNodesUntilItFindsAMatch() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		/** pick a random node from the array */
		$testNode = $nodeArray[5];

		$this->tree->setNodes($nodeArray);
		self::assertTrue($this->tree->hasNode($testNode));
	}

	/**
	 * testHasNodeReturnsFalseIfItDoesNotFindAMatch
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasNode
	 */
	public function testHasNodeReturnsFalseIfItDoesNotFindAMatch() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		$nodeData = $this->fixture->makeSingleNodeRowWithRootAsParent();
		$node = $this->makeNode($nodeData);
		self::assertFalse($this->abstractTree->hasNode($node));
	}

	/**
	 * testIsEmpty
	 * @covers \pvc\struct\tree\tree\TreeAbstract::isEmpty
	 */
	public function testIsEmpty() : void
	{
		self::assertTrue($this->tree->isEmpty());
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($node_1);
		self::assertFalse($this->tree->isEmpty());
	}

	/**
	 * testNodeCount
	 * @covers \pvc\struct\tree\tree\TreeAbstract::nodeCount
	 */
	public function testNodeCount() : void
	{
		self::assertEquals(0, $this->tree->nodeCount());

		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->tree->addNode($node_1);
		self::assertEquals(1, $this->tree->nodeCount());

		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$this->tree->addNode($node_2);
		self::assertEquals(2, $this->tree->nodeCount());
	}

	/**
	 * testDeleteNodeRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::verifyDeleteNodeInitialConditions
	 * @covers \pvc\struct\tree\tree\Tree::deleteNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
    public function testDeleteNodeThrowsExceptionWhenNodeIsNotInTree(): void
    {
	    /**
	     * deleteNode calls hasNode, which uses this method to ensure that the node we are trying to delete is
	     * actually in the tree.
	     */
	    $node = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$this->expectException(NodeNotInTreeException::class);
        $this->tree->deleteNode($node);
    }

	/**
	 * testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse
	 * @covers \pvc\struct\tree\tree\TreeAbstract::verifyDeleteNodeInitialConditions
	 * @covers \pvc\struct\tree\tree\Tree::deleteNode
	 * @covers \pvc\struct\tree\tree\TreeOrdered::deleteNode
	 */
	public function testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse() : void
	{
		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		$deleteBranch = false;
		$this->expectException(DeleteInteriorNodeException::class);
		$this->tree->deleteNode($node_1, $deleteBranch);
	}

	/**
	 * deleteNodeRecurse is tested in TreeTest in a tree that does not have ListOrdered objects handling the children
	 * for each node.  In other words, testing for the "recursive" part is done in TreeTest.  As far as testing to
	 * insure that the child lists are properly cleaned up during deletion, that testing is done in TreeOrderedTest.
	 */
	/**
	 * testVerifyTreeSearchInitialConditionsSetsDefaultStartNodeToRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::verifyTreeSearchInitialConditions
	 */
	public function testVerifyTreeSearchInitialConditionsSetsDefaultsProperly() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
		 * verify that the number of nodes returning from the search equals the total number of nodes in the abstractTree to
		 * begin with.  So the callback was defaulted properly because it always returns true
		 */
		self::assertEquals($this->tree->nodeCount(), count($searchResult));
	}

	/**
	 * testVerifyInitialConditionsThrowsExceptionWithStartNodeNotInTree
	 * @covers \pvc\struct\tree\tree\TreeAbstract::verifyTreeSearchInitialConditions
	 */
	public function testVerifyInitialConditionsThrowsExceptionWithStartNodeNotInTree() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);
		$startNode = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		$this->expectException(NodeNotInTreeException::class);
		$nodeList = $this->tree->getTreeDepthFirst($startNode);
	}

	/**
	 * tests that the search returns all nodes in the correct order when starting from the root
	 *
	 * testGetTreeDepthFirstFullTree
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirstRecurse
	 */
	public function testGetTreeDepthFirstFullTree(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * tests that the search returns partial abstractTree in the correct order when starting from interior node with nodeid = 2
	 *
	 * testGetTreeDepthFirstFromBranchNode
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirstRecurse
	 */
	public function testGetTreeDepthFirstFromBranchNode(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeDepthFirstRecurse
	 */
	public function testGetTreeDepthFirstWhereCallbackDoesNotAlwaysReturnTrue(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		/**
		 * In TreeOrderedTest, this sets up all the mocking for children
		 * In TreeTest, this is an empty method that does nothing.
		 */
		$this->setFixtureForTreeSearches();

		$callback = function($node) {
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirstRecurse
	 */
	public function testGetTreeBreadthFirstFullTree(): void
	{
		$expectedResult = array_values($this->makeBreadthFirstArrayOfAllNodeIds());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirstRecurse
	 */
	public function testGetTreeBreadthFirstPartialFromNodeIdOne(): void
	{
		$expectedResult = array_values($this->makeBreadthFirstArrayStartingAtNodeid1());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirst
	 */
	public function testGetTreeBreadthFirstThrowsExceptionWithBadMaxLevelsParameter() : void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);
		$this->expectException(BadTreesearchLevelsException::class);
		$nodes = $this->tree->getTreeBreadthFirst(null, null, -2);

	}
	/**
	 * testGetTreeBreadthFirstPartialTwoLevelsFromRoot
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirst
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeBreadthFirstRecurse
	 */
	public function testGetTreeBreadthFirstPartialTwoLevelsFromRoot(): void
	{
		$expectedResult = array_values($this->makeBreadthFirstArrayTwoLevelsStartingAtRoot());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		/**
		 * In TreeOrderedTest, this sets up all the mocking for children
		 * In TreeTest, this is an empty method that does nothing.
		 */
		$this->setFixtureForTreeSearches();

		$startNode = $this->abstractTree->getRoot();
		$levels = 2;
		$callback = function ($node) { return $node->getNodeId(); };
		$actualResult = array_values(array_map($callback, $this->tree->getTreeBreadthFirst($startNode, null, $levels)));

		self::assertEquals($expectedResult, $actualResult);
	}

	/**
	 * testGetNode
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getNode
	 */
	public function testGetNode() : void
	{
		$nonExistentNodeid = 8;
		self::assertNull($this->tree->getNode($nonExistentNodeid));

		$node_1 = $this->makeNode($this->fixture->makeRootNodeRowWithGoodData());
		$node_2 = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());

		$this->tree->addNode($node_1);
		$this->tree->addNode($node_2);

		self::assertEquals($node_1, $this->tree->getNode($node_1->getNodeId()));
		self::assertEquals($node_2, $this->tree->getNode($node_2->getNodeId()));
	}

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
	     * need to change the equals expectations on each node so that when the abstractTree calls getChildren with leaf as
	     * its argument, the abstractTree knows that leaf is in the abstractTree.
	     */
	    $nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		$leafNodeId = 12;
        $leaf = $this->tree->getNode($leafNodeId);
        self::assertEmpty($this->tree->getChildrenOf($leaf));
    }

	/**
	 * testing for getChildrenOf is continued separately in TreeTest and TreeOrderedTest because in TreeOrderedTest
	 * we need to create a mock for the child list and set up some expectations.
	 */

	/**
	 * testGetParentOfThrowsExceptionIfNodeNotInTree
	 * @covers \pvc\struct\tree\tree\Tree::getParentOf
	 * @covers \pvc\struct\tree\tree\TreeOrdered::getParentOf
	 */
    public function testGetParentOfThrowsExceptionIfNodeNotInTree(): void
    {
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
	    /**
	     * node has not been added to the abstractTree
	     */
        $this->expectException(NodeNotInTreeException::class);
		$parent = $this->tree->getParentOf($node);
    }

	/**
	 * testing for getChildrenOf is continued separately in TreeTest and TreeOrderedTest because in TreeOrderedTest
	 * we need to create stub for the call to getParent and set up some expectations.
	 */


	/**
	 * testHasLeafReturnsFalseOnLeafThatIsNotinTree
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasLeafWithId
	 */
	public function testHasLeafWithIdReturnsFalseOnLeafThatIsNotinTree(): void
	{
		$nonExistentNodeId = 99;
		self::assertFalse($this->abstractTree->hasLeafWithId($nonExistentNodeId));
	}

	/**
	 * testHasLeafWithId
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasLeafWithId
	 */
	public function testHasLeafWithId(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);
		self::assertFalse($this->tree->hasLeafWithId(0));
		self::assertFalse($this->tree->hasLeafWithId(5));
		self::assertTrue($this->tree->hasLeafWithId(12));
	}

	/**
	 * testGetLeaves
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getLeaves
	 */
	public function testGetLeaves(): void
	{
		self::assertIsArray($this->abstractTree->getLeaves());
		self::assertEmpty($this->abstractTree->getLeaves());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
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
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasInteriorNodeWithId
	 */
	public function testHasInteriorNodeWithIdReturnsFalseWhenNotInTree(): void
	{
		$node = $this->makeNode($this->fixture->makeSingleNodeRowWithRootAsParent());
		self::assertFalse($this->tree->hasInteriorNodeWithId($node->getNodeId()));
	}

	/**
	 * testHasInteriorNodeWithId
	 * @covers \pvc\struct\tree\tree\TreeAbstract::hasInteriorNodeWithId
	 */
	public function testHasInteriorNodeWithId(): void
	{
		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		self::assertTrue($this->tree->hasInteriorNodeWithId(0));
		self::assertTrue($this->tree->hasInteriorNodeWithId(5));
		self::assertFalse($this->tree->hasInteriorNodeWithId(12));
	}

	/**
	 * testGetInteriorNodes
	 * @covers \pvc\struct\tree\tree\TreeAbstract::getInteriorNodes
	 */
	public function testGetInteriorNodes(): void
	{
		self::assertIsArray($this->abstractTree->getInteriorNodes());
		self::assertEmpty($this->abstractTree->getInteriorNodes());

		$nodeArray = $this->makeNodeArray($this->fixture->makeArrayOfNodeIdsForTree());
		$this->tree->setNodes($nodeArray);

		$expectedResult = $this->fixture->makeArrayOfGoodDataInteriorNodeIds();

		$nodeArray = $this->tree->getInteriorNodes();

		$actualResult = [];
		foreach ($nodeArray as $node) {
			$actualResult[] = $node->getNodeId();
		}
		self::assertEqualsCanonicalizing($expectedResult, $actualResult);
	}

}
