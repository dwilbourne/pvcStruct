<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\tree\node\Treenode;

/**
 * @template TreenodeType of TreenodeInterface
 */
class TreenodeTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $nodeId = 1;

    /**
     * @var non-negative-int
     */
    protected int $parentId = 2;

    /**
     * @var non-negative-int
     */
    protected int $treeId = 1;

    /**
     * @var TreenodeInterface<TreenodeType>
     */
    protected TreenodeInterface $node;

    /**
     * @var TreenodeInterface<TreenodeType>
     */
    protected TreenodeInterface $parent;

    /**
     * @var TreenodeChildCollectionInterface<TreenodeType>
     */
    protected TreenodeChildCollectionInterface $childrenOfNode;

    /**
     * @var TreenodeChildCollectionInterface<TreenodeType>
     */
    protected TreenodeChildCollectionInterface $childrenOfParent;

    /**
     * @var TreenodeChildCollectionFactoryInterface<TreenodeType>
     */
    protected TreenodeChildCollectionFactoryInterface $childrenOfNodeCollectionFactory;

    /**
     * @var TreenodeChildCollectionFactoryInterface<TreenodeType>
     */
    protected TreenodeChildCollectionFactoryInterface $childrenOfParentCollectionFactory;

    /**
     * @var TreeInterface<TreenodeType>
     */
    protected TreeInterface $tree;

    public function setUp(): void
    {
        $this->tree = $this->createMock(TreeInterface::class);

        $this->childrenOfNode = $this->createMock(TreenodeChildCollectionInterface::class);
        $this->childrenOfParent = $this->createMock(TreenodeChildCollectionInterface::class);

        $this->childrenOfNodeCollectionFactory = $this->createMock(TreenodeChildCollectionFactoryInterface::class);
        $this->childrenOfNodeCollectionFactory->method('makeChildCollection')->willReturn($this->childrenOfNode);

        $this->childrenOfParentCollectionFactory = $this->createMock(TreenodeChildCollectionFactoryInterface::class);
        $this->childrenOfParentCollectionFactory->method('makeChildCollection')->willReturn($this->childrenOfParent);

        $this->node = new Treenode($this->childrenOfNodeCollectionFactory);
        $this->node->setNodeId($this->nodeId);
        $this->node->setTree($this->tree);

        $this->parent = $this->createMock(Treenode::class);
        $this->parent->method('getNodeId')->willReturn($this->parentId);
        $this->parent->method('getChildren')->willReturn($this->childrenOfParent);

    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\node\Treenode::__construct
     * @covers \pvc\struct\tree\node\Treenode::getChildren
     * @covers \pvc\struct\tree\node\Treenode::getChildrenArray
     */
    public function testConstructAndGetChildren(): void
    {
        self::assertInstanceOf(TreenodeInterface::class, $this->node);
        self::assertSame($this->childrenOfNode, $this->node->getChildren());
        self::assertIsArray($this->node->getChildrenArray());
    }

    /**
     * testSetNodeIdFailsWithInvalidNodeId
     *
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\Treenode::setNodeId
     */
    public function testSetNodeIdFailsWithInvalidNodeId(): void
    {
        $badNodeId = -2;
        $node = new Treenode($this->childrenOfNodeCollectionFactory);
        self::expectException(InvalidNodeIdException::class);
        $node->setNodeId($badNodeId);
    }

    /**
     * @return void
     * @throws InvalidNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @covers \pvc\struct\tree\node\Treenode::setNodeId
     */
    public function testNodeIdIsImmutable(): void
    {
        $newNodeId = 2;
        self::expectException(NodeNotEmptyHydrationException::class);
        $this->node->setNodeId($newNodeId);
    }

    /**
     * @return void
     * @throws InvalidNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @covers \pvc\struct\tree\node\Treenode::setNodeId
     * @covers \pvc\struct\tree\node\Treenode::getNodeId
     */
    public function testSetGetNodeId(): void
    {
        self::assertEquals($this->nodeId, $this->node->getNodeId());
    }

    /**
     * @return void
     * @throws CircularGraphException
     * @throws InvalidParentNodeIdException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @covers \pvc\struct\tree\node\Treenode::setTree
     * @covers \pvc\struct\tree\node\Treenode::isRoot
     */
    public function testSetTreeAndIsRoot(): void
    {
        $this->tree->expects($this->once())->method('getRoot')->willReturn($this->node);
        self::assertTrue($this->node->isRoot());
    }

    /**
     * @return void
     * @throws SetTreeException
     * @covers \pvc\struct\tree\node\Treenode::setTree
     */
    public function testTreePropertyIsImmutable(): void
    {
        $tree2 = $this->createMock(TreeInterface::class);
        self::expectException(SetTreeException::class);
        $this->node->setTree($tree2);
    }

    /**
     * @return void
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsIfParentIsNotInTree(): void
    {
        $this->tree = $this->createMock(TreeInterface::class);
        $this->tree->method('getNode')->with($this->parentId)->willReturn(null);

        self::expectException(InvalidParentNodeIdException::class);
        $this->node->setParent($this->parent);

    }

    /**
     * @return void
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWithCircularGraph(): void
    {
        $this->parent->method('isDescendantOf')->with($this->node)->willReturn(true);
        $this->tree->method('getNode')->with($this->parentId)->willReturn($this->parent);
        self::expectException(CircularGraphException::class);
        $this->node->setParent($this->parent);
    }

    /**
     * @return void
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWhenTryingToMoveRootNode(): void
    {
        $this->parent->method('isDescendantOf')->with($this->node)->willReturn(false);
        $this->tree->method('getNode')->with($this->parentId)->willReturn($this->parent);
        $this->tree->method('getRoot')->willReturn($this->node);

        self::expectException(RootCannotBeMovedException::class);
        $this->node->setParent($this->parent);
    }

    /**
     * @return void
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @covers \pvc\struct\tree\node\Treenode::setParent
     * @covers \pvc\struct\tree\node\Treenode::getParent
     */
    public function testSetParentAddsNodeToChildCollectionOfParent(): void
    {
        $this->parent->method('isDescendantOf')->with($this->node)->willReturn(false);
        $this->parent->method('getChildren')->willReturn($this->childrenOfParent);

        $mockRoot = $this->createMock(Treenode::class);

        $this->tree->method('getNode')->with($this->parentId)->willReturn($this->parent);
        $this->tree->method('getRoot')->willReturn($mockRoot);

        $this->childrenOfParent->expects($this->once())->method('add')->with($this->nodeId, $this->node);
        $this->node->setParent($this->parent);
        self::assertSame($this->parent, $this->node->getParent());
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::setIndex
     * @covers \pvc\struct\tree\node\Treenode::getIndex
     */
    public function testSetGetIndex(): void
    {
        $index = 3;
        $this->node->setIndex($index);
        self::assertSame($index, $this->node->getIndex());
    }


    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getFirstChild
     */
    public function testGetFirstChild(): void
    {
        $this->childrenOfNode->expects($this->once())->method('getFirst');
        $this->node->getFirstChild();
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getLastChild
     */
    public function testGetLastChild(): void
    {
        $this->childrenOfNode->expects($this->once())->method('getLast');
        $this->node->getLastChild();
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getNthChild
     */
    public function testGetNthChild(): void
    {
        $n = 2;
        $this->childrenOfNode->expects($this->once())->method('getNth')->with($n);
        $this->node->getNthChild($n);
    }

    /**
     * testIsLeafOnNodeWithNoChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenWithNoChildren(): void
    {
        $this->childrenOfNode->method('isEmpty')->willReturn(true);
        self::assertFalse($this->node->hasChildren());
    }

    /**
     * testIsLeafOnNodeWithChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenWithChildren(): void
    {
        $parent = new Treenode($this->childrenOfParentCollectionFactory);
        $this->childrenOfParent->method('isEmpty')->willReturn(false);
        self::assertTrue($parent->hasChildren());
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::getChild
     */
    public function testGetChildReturnsNullWithNoChildren(): void
    {
        $this->childrenOfNode->method('getElement')->with($this->nodeId)->willReturn(null);
        self::assertNull($this->node->getChild($this->nodeId));
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::getChild
     */
    public function testGetChildReturnsChild(): void
    {
        $childNodeId = 5;
        $childNode = $this->createMock(Treenode::class);
        $this->childrenOfNode->method('getElement')->with($childNodeId)->willReturn($childNode);
        self::assertSame($childNode, $this->node->getChild($childNodeId));
    }

    /**
     * testGetSiblingsForTreeWithRootOnly
     * logic for getting siblings for the root is different from any other node because the root has no parent.
     *
     * @covers \pvc\struct\tree\node\Treenode::getSiblings
     */
    public function testGetSiblingsForRoot(): void
    {
        $root = new Treenode($this->childrenOfParentCollectionFactory);
        $root->setNodeId($this->parentId);
        $root->setTree($this->tree);
        $root->setParent(null);

        $this->tree->method('getRoot')->willReturn($root);

        $this->childrenOfParentCollectionFactory->expects($this->once())->method('makeChildCollection')->willReturn($this->childrenOfParent);
        $this->childrenOfParent->expects($this->once())->method('add')->with($this->parentId, $root);

        $root->getSiblings();
    }

    /**
     * testGetSiblingsForChildrenOfRoot
     *
     * @covers \pvc\struct\tree\node\Treenode::getSiblings
     */
    public function testGetSiblingsForChildrenOfRoot(): void
    {
        $this->parent->method('isDescendantOf')->with($this->node)->willReturn(false);
        $this->parent->method('getChildren')->willReturn($this->childrenOfParent);

        $mockRoot = $this->createMock(Treenode::class);

        $this->tree->method('getNode')->with($this->parentId)->willReturn($this->parent);
        $this->tree->method('getRoot')->willReturn($mockRoot);

        $this->childrenOfParent->expects($this->once())->method('add')->with($this->nodeId, $this->node);
        $this->node->setParent($this->parent);

        self::assertSame($this->childrenOfParent, $this->node->getSiblings());
    }

}
