<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree;

use PHPUnit\Framework\TestCase;
use pvc\struct\lists\ListOrdered;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeDataException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\iface\tree\TreeInterface;
use pvc\validator\numeric\ValidatorIntegerNonNegative;
use tests\struct\tree\fixture\TreeTestFixture;

abstract class TreeTest extends TestCase
{
    /** @phpstan-ignore-next-line */
    protected $tree;

    protected int $treeid;
    protected TreeTestFixture $fixture;

    /** @phpstan-ignore-next-line */
    abstract protected function makeNode(int $nodeid);

    abstract protected function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne() : array;
    abstract protected function makeDepthFirstArrayOfAllNodeIds() : array;
    abstract protected function makeDepthFirstArrayOfBranchAtNodeid2() : array;
    abstract protected function makeBreadthFirstArrayOfAllNodeIds() : array;
    abstract protected function makeBreadthFirstArrayStartingAtNodeid1() : array;
    abstract protected function makeBreadthFirstArrayTwoLevelsStartingAtRoot() : array;

    public function testSetGetTreeId() : void
    {
        $treeid = 3;
        $this->tree->setTreeId($treeid);
        self::assertEquals($treeid, $this->tree->getTreeId());
    }

    public function testSetGetDeleteRoot() : void
    {
        $node = $this->makeNode(0);
        $node->setTreeId($this->treeid);
        $this->tree->addNode($node);
        static::assertFalse($this->tree->isEmpty());
        static::assertEquals(1, $this->tree->nodeCount());
        static::assertEquals(0, $this->tree->getRoot()->getNodeId());

        $this->tree->deleteNode($node, true);
        static::assertTrue($this->tree->isEmpty());
        static::assertNull($this->tree->getRoot());
    }

    public function testAlreadySetRootException() : void
    {
        $node = $this->makeNode(0);
        $node->setTreeId($this->treeid);
        $this->tree->addNode($node);

        $node_2 = $this->makeNode(1);
        $node_2->setTreeId($this->treeid);

        self::expectException(AlreadySetRootException::class);
        $this->tree->addNode($node_2);
    }

    public function testHasNodeGetNode() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $node = $this->tree->getNode(5);
        self::assertTrue($this->tree->hasNode($node));

        self::assertNull($this->tree->getNode(99));

        $node = $this->makeNode(99);
        self::assertFalse($this->tree->hasNode($node));
    }

    public function testHydrationWithGoodData() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $nodeCount = count($goodNodeCollection);
        $this->tree->hydrateNodes($goodNodeCollection);
        static::assertEquals($nodeCount, $this->tree->nodeCount());
        static::assertEquals($this->fixture->getRootNodeId(), $this->tree->getRoot()->getNodeId());
        static::assertEquals($nodeCount, count($this->tree->getNodes()));
    }

    public function testDehydrate() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);
        $expectedResult = [];
        foreach ($goodNodeCollection as $node) {
            $expectedResult[$node->getNodeId()] = $node->dehydrate();
        }
        asort($expectedResult);
        $actualResult = $this->tree->dehydrateNodes();
        asort($actualResult);
        self::assertEquals($expectedResult, $actualResult);
    }

    public function testHydrationWithInvalidNodeData() : void
    {
        $invalidNodeCollection = [1, 2, 3, 4, 5];
        self::expectException(InvalidNodeDataException::class);
        $this->tree->hydrateNodes($invalidNodeCollection);
    }

    public function testHydrationWithBadParentData() : void
    {
        $badParentDataCollection = $this->fixture->makeTreeWithNonExistentParentData();
        self::expectException(InvalidParentNodeException::class);
        $this->tree->hydrateNodes($badParentDataCollection);
    }

    public function testHydrationWithDoubleRootData() : void
    {
        $doubleRootCollection = $this->fixture->makeTreeWithMultipleRoots();
        self::expectException(AlreadySetRootException::class);
        $this->tree->hydrateNodes($doubleRootCollection);
    }

    public function testHydrationCircularGraphData() : void
    {
        $circularGraphCollection = $this->fixture->makeTreeWithCircularParents();
        self::expectException(CircularGraphException::class);
        $this->tree->hydrateNodes($circularGraphCollection);
    }

    public function testGetChildrenArrayOfException() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);
        $invalidParent = $this->makeNode(99);
        self::expectException(NodeNotInTreeException::class);
        $array = $this->tree->getChildrenOf($invalidParent);
    }

    public function testGetChildrenOf() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        // expectedResult will be ordered differently depending on whether the test being run is for
        // a tree with unordered nodes or ordered nodes
        $expectedResult = $this->makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOne();

        // getChildren returns nodes in different orders depending on whether the tree being tested
        // has ordered or unordered nodes
        $node = $this->tree->getNode(1);
        $iterable = $this->tree->getChildrenOf($node);
        $actualResult = [];
        foreach ($iterable as $node) {
            $actualResult[] = $node->getNodeId();
        }
        self::assertEquals($expectedResult, $actualResult);

        // now test a leaf
        $node = $this->tree->getNode(12);
        $actualResult = $this->tree->getChildrenOf($node);
        self::assertEmpty($actualResult);
    }

    public function testGetParentOf() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);
        $expectedResult = $this->tree->getNode(5);
        $node = $this->tree->getNode(12);
        self::assertEquals($expectedResult, $this->tree->getParentOf($node));

        $root = $this->tree->getRoot();
        self::assertNull($this->tree->getParentOf($root));
    }

    /** @phpstan-ignore-next-line */
    public function testAddNodeRoot()
    {
        $node = $this->makeNode(0);
        $node->setTreeId($this->treeid);
        $node->setValue('foo');
        $this->tree->addNode($node);

        self::assertEquals(1, $this->tree->nodeCount());
        self::assertEquals($node, $this->tree->getRoot());
        return $this->tree;
    }

    /**
     * @function testAddNode
     * @param TreeInterface $tree
     * @depends testAddNodeRoot
     */
    public function testAddNode($tree) : void
    {
        $node = $this->makeNode(1);
        $node->setParentId(0);
        $node->setTreeId($this->treeid);
        $node->setValue('bar');
        $tree->addNode($node);

        self::assertEquals(2, $tree->nodeCount());
        $parent = $tree->getNode($node->getParentId());
        self::assertEquals($parent, $tree->getRoot());
    }

    public function testAddNodeExceptionNodeAlreadyInTree() : void
    {
        $node = $this->makeNode(0);
        $node->setTreeId($this->treeid);
        $node->setValue('foo');
        $this->tree->addNode($node);

        $this->expectException(AlreadySetNodeidException::class);
        $this->tree->addNode($node);
    }

    public function testAddNodeExceptionNodeHasWrongTreeId() : void
    {
        $node = $this->makeNode(1);
        $node->setParentId(0);
        $node->setTreeId($this->treeid + 1);
        $node->setValue('bar');
        self::expectException(InvalidTreeidException::class);
        $this->tree->addNode($node);
    }

    public function testAddNodeExceptionParentDoesNotExistInTree() : void
    {
        $node = $this->makeNode(0);
        $node->setParentId(1);
        $node->setTreeId($this->treeid);
        $node->setValue('foo');
        $this->expectException(InvalidParentNodeException::class);
        $this->tree->addNode($node);
    }

    public function testHasLeafWithId() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        self::assertFalse($this->tree->hasLeafWithId(0));
        self::assertFalse($this->tree->hasLeafWithId(5));
        self::assertTrue($this->tree->hasLeafWithId(12));
    }

    public function testHasInteriorNodeWithId() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        self::assertTrue($this->tree->hasInteriorNodeWithId(0));
        self::assertTrue($this->tree->hasInteriorNodeWithId(5));
        self::assertFalse($this->tree->hasInteriorNodeWithId(12));
    }

    public function testDeleteNode() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);
        $nodeCount = count($goodNodeCollection);
        self::assertEquals($nodeCount, $this->tree->nodeCount());

        $node = $this->tree->getNode(12);
        $this->tree->deleteNode($node);
        $nodeCount--;
        self::assertEquals($nodeCount, $this->tree->nodeCount());

        $node = $this->tree->getNode(11);
        $this->tree->deleteNode($node);
        $nodeCount--;
        self::assertEquals($nodeCount, $this->tree->nodeCount());
    }

    public function testDeleteBranch() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $node = $this->tree->getNode(5);
        $this->tree->deleteNode($node, true);
        // node 5 has 4 children (9, 10, 11 & 12) so removing 5 plus its children is a total of 5 nodes removed
        $expectedNodeCount = count($goodNodeCollection) - 5;
        self::assertEquals($expectedNodeCount, $this->tree->nodeCount());
    }

    public function testDeleteNonExistentNode() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $this->expectException(NodeNotInTreeException::class);
        $node = $this->makeNode(54);
        $node->setParentId(10);
        $node->setTreeId(99);
        $this->tree->deleteNode($node);
    }

    public function testDeleteInteriorNodeException() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $this->expectException(DeleteInteriorNodeException::class);
        $node = $this->tree->getNode(5);
        $this->tree->deleteNode($node);
    }

    public function testGetTreeDepthFirstFullTree() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);
        $nodeArray = $this->tree->getTreeDepthFirst();
        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }
        $expectedResult = $this->makeDepthFirstArrayOfAllNodeIds();
        static::assertEquals($expectedResult, $actualResult);
    }

    public function testGetTreeDepthFirstPartialTree() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $branchNodeId = 2;
        $nodeArray = $this->tree->getTreeDepthFirst($this->tree->getNode($branchNodeId));
        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }
        $expectedResult = $this->makeDepthFirstArrayOfBranchAtNodeid2();
        static::assertEquals($expectedResult, $actualResult);
    }

    public function testGetTreeDepthFirstException() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $nodeIdThatDoesNotExistInTree = 99;
        $node = $this->makeNode($nodeIdThatDoesNotExistInTree);
        $this->expectException(NodeNotInTreeException::class);
        $this->tree->getTreeDepthFirst($node);
    }

    public function testGetTreeBreadthFirstFullTree() : void
    {
        $expectedResult = $this->makeBreadthFirstArrayOfAllNodeIds();

        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $nodeArray = $this->tree->getTreeBreadthFirst();

        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }

        static::assertEquals($expectedResult, $actualResult);
    }

    public function testGetTreeBreadthFirstPartialFromNodeIdOne() : void
    {
        $expectedResult = $this->makeBreadthFirstArrayStartingAtNodeid1();

        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $startNodeId = 1;
        $nodeArray = $this->tree->getTreeBreadthFirst($this->tree->getNode($startNodeId));

        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }

        static::assertEquals($expectedResult, $actualResult);
    }

    public function testGetTreeBreadthFirstPartialTwoLevelsFromRoot() : void
    {
        $expectedResult = $this->makeBreadthFirstArrayTwoLevelsStartingAtRoot();

        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $startNode = $this->tree->getRoot();
        $levels = 2;
        $nodeArray = $this->tree->getTreeBreadthFirst($startNode, null, $levels);

        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }

        static::assertEquals($expectedResult, $actualResult);
    }

    public function testGetTreeBreadthFirstException() : void
    {
        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $nodeIdThatDoesNotExistInTree = 99;
        $node = $this->makeNode($nodeIdThatDoesNotExistInTree);
        $this->expectException(NodeNotInTreeException::class);
        $this->tree->getTreeBreadthFirst($node);
    }

    public function testGetLeaves() : void
    {
        $expectedResult = $this->fixture->makeArrayOfGoodDataLeafNodeIds();

        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $nodeArray = $this->tree->getLeaves();

        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }
        static::assertEquals(asort($expectedResult), asort($actualResult));
    }

    public function testGetInteriorNodes() : void
    {
        $expectedResult = $this->fixture->makeArrayOfGoodDataInteriorNodeIds();

        $goodNodeCollection = $this->fixture->makeTreeWithGoodData();
        $this->tree->hydrateNodes($goodNodeCollection);

        $nodeArray = $this->tree->getInteriorNodes();

        $actualResult = [];
        foreach ($nodeArray as $node) {
            $actualResult[] = $node->getNodeId();
        }
        static::assertEquals(asort($expectedResult), asort($actualResult));
    }

    public function testEmptyTree() : void
    {
        static::assertTrue($this->tree->isEmpty());
        static::assertEquals(0, $this->tree->nodeCount());
    }

    public function testGetChildrenOfException() : void
    {
        $node = $this->makeNode(200);
        self::expectException(NodeNotInTreeException::class);
        $array = $this->tree->getChildrenOf($node);
    }

    public function testGetParentOfException() : void
    {
        $node = $this->makeNode(200);
        self::expectException(NodeNotInTreeException::class);
        $foo = $this->tree->getParentOf($node);
    }

    public function testHasLeafWithIdException() : void
    {
        $node = $this->makeNode(1);
        self::assertFalse($this->tree->hasLeafWithId($node->getNodeId()));
    }

    public function testHasInteriorNodeWithIdException() : void
    {
        $node = $this->makeNode(1);
        self::assertFalse($this->tree->hasInteriorNodeWithId($node->getNodeId()));
    }
}
