<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node;

use Mockery;
use PHPUnit\Framework\TestCase;
use pvc\struct\lists\ListOrdered;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\iface\node\TreenodeOrderedInterface;
use pvc\struct\tree\iface\tree\TreeOrderedInterface;
use pvc\struct\tree\node\TreenodeOrdered;

class TreenodeOrderedTest extends TestCase
{
    /** @phpstan-ignore-next-line */
    protected $tree;
    protected TreenodeOrderedInterface $parent;
    protected TreenodeOrderedInterface $node;
    protected TreenodeOrderedInterface $childOne;
    protected TreenodeOrderedInterface $childTwo;

    public function setUp(): void
    {
        $treeid = 2;
        $this->tree = Mockery::mock(TreeOrderedInterface::class);
        $this->tree->shouldReceive('getTreeId')->withNoArgs()->andReturn($treeid);

        $this->parent = $this->makeNode(0, null);
        $this->node = $this->makeNode(1, 0);
        $this->childOne = $this->makeNode(8, 1);
        $this->childTwo = $this->makeNode(6, 1);

        $this->tree->shouldReceive('getNode')->with($this->parent->getNodeId())->andReturn($this->parent);
        $this->tree->shouldReceive('getNode')->with($this->node->getNodeId())->andReturn($this->node);
        $this->tree->shouldReceive('getNode')->with($this->childOne->getNodeId())->andReturn($this->childOne);
        $this->tree->shouldReceive('getNode')->with($this->childTwo->getNodeId())->andReturn($this->childTwo);

        $this->tree->shouldReceive('getRoot')->withNoArgs()->andReturn($this->parent);
    }

    public function setAllReferences() : void
    {
        $this->parent->setReferences($this->tree);
        $this->node->setReferences($this->tree);
        $this->childOne->setReferences($this->tree);
        $this->childTwo->setReferences($this->tree);
    }

    public function makeNode(int $nodeid, ?int $parentid): TreenodeOrderedInterface
    {
        $node = new TreenodeOrdered($nodeid, new ListOrdered());
        $node->setParentId($parentid);
        $node->setTreeId($this->tree->getTreeId());
        return $node;
    }

    public function testSetGetHydrationIndex() : void
    {
        $hydrationIndex = 5;
        $this->node->setHydrationIndex($hydrationIndex);
        self::assertEquals($hydrationIndex, $this->node->getHydrationIndex());
    }

    public function testSetGetIndex() : void
    {
        $this->setAllReferences();

        self::assertEquals(0, $this->parent->getIndex());
        // verify that trying to set index of a node with no siblings (for example, the root node)
        // does not produce an error but leaves the index at 0
        $this->parent->setIndex(5);
        self::assertEquals(0, $this->parent->getIndex());

        self::assertEquals(0, $this->childOne->getIndex());
        self::assertEquals(1, $this->childTwo->getIndex());
        $this->childOne->setIndex(1);
        self::assertEquals(1, $this->childOne->getIndex());
        self::assertEquals(0, $this->childTwo->getIndex());
    }

    public function testSetIndexException() : void
    {
        $this->setAllReferences();
        $childThree = $this->makeNode(14, 1);
        $childThree->setTreeId(5);
        self::expectException(NodeNotInTreeException::class);
        $childThree->setIndex(1);
    }

    public function testGetIndexOnNodeWithoutReferencesSet() : void
    {
        self::expectException(NodeNotInTreeException::class);
        $this->childOne->getIndex();
    }

    public function testHydrate() : void
    {
        $row = ['nodeid' => 0, 'index' => 5, 'parentid' => null, 'treeid' => 2, 'value' => 'some string'];
        $node = new TreenodeOrdered(0, new ListOrdered());
        $node->hydrate($row);
        self::assertEquals(5, $node->getHydrationIndex());
    }

    public function testDehydrate() : void
    {
        $expectedResult = ['nodeid' => 0, 'parentid' => null, 'treeid' => 2, 'value' => null, 'index' => 0];
        self::assertEquals($expectedResult, $this->parent->dehydrate());
    }

    public function testSetReferencesException() : void
    {
        $newTree = Mockery::mock(TreeOrderedInterface::class);
        $newTree->shouldReceive('getNode')->with($this->node->getNodeId())->andReturnNull();
        self::expectException(NodeNotInTreeException::class);
        $this->node->setReferences($newTree);
    }

    public function testReferences() : void
    {
        $this->node->setReferences($this->tree);
        self::assertTrue($this->node->hasReferencesSet());
        self::assertSame($this->parent, $this->node->getParent());
        self::assertSame($this->tree, $this->node->getTree());

        // now unset all the references
        $this->node->unsetReferences();
        self::assertNull($this->node->getParent());
        self::assertNull($this->node->getParentId());
        self::assertNull($this->node->getTree());
        self::assertNull($this->node->getTreeId());
        // nodeid remains set - immutable
        self::assertEquals(1, $this->node->getNodeId());
    }

    public function testGetChild() : void
    {
        $this->setAllReferences();
        self::assertEquals($this->childTwo, $this->node->getChild($this->childTwo->getNodeId()));
        self::assertNull($this->node->getChild($this->parent->getNodeId()));
    }

    public function testGetChildrenArray() : void
    {
        $this->setAllReferences();
        $expectedResult = [$this->childOne, $this->childTwo];
        self::assertEqualsCanonicalizing($expectedResult, $this->node->getChildrenArray());
    }

    public function testIsLeaf() : void
    {
        $this->setAllReferences();
        self::assertTrue($this->childTwo->isLeaf());
        self::assertFalse($this->node->isLeaf());
    }

    public function testIsInteriorNode() : void
    {
        $this->setAllReferences();
        self::assertFalse($this->childTwo->isInteriorNode());
        self::assertTrue($this->node->isInteriorNode());
    }

    public function testIsRootTreeNotSet() : void
    {
        // node is not root if tree is not set, even if parent is null
        self::assertFalse($this->node->isRoot());
    }

    public function testIsRoot() : void
    {
        $this->setAllReferences();
        self::assertTrue($this->parent->isRoot());
        self::assertFalse($this->node->isRoot());
    }

    public function testIsDescendantOf() : void
    {
        $this->setAllReferences();
        self::assertFalse($this->parent->isDescendantOf($this->node));
        self::assertTrue($this->node->isDescendantOf($this->parent));
        self::assertTrue($this->childTwo->isDescendantOf($this->parent));
    }

    public function testIsAncestorOf() : void
    {
        $this->setAllReferences();
        self::assertTrue($this->parent->isAncestorOf($this->node));
        self::assertFalse($this->node->isAncestorOf($this->parent));
    }

    public function testConstruct() : void
    {
        $this->setAllReferences();
        self::assertInstanceOf(ListOrdered::class, $this->node->getChildren());
    }
}
