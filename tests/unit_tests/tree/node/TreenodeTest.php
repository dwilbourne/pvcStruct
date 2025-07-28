<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\InvalidValueException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\tree\node\Treenode;
use pvc\testingutils\testingTraits\IteratorTrait;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixture;

use function PHPUnit\Framework\assertNull;

class TreenodeTest extends TestCase
{
    use IteratorTrait;

    /**
     * @var TreenodeTestingFixture
     */
    protected TreenodeTestingFixture $fixture;

    /**
     * @var CollectionInterface&MockObject
     */
    protected CollectionInterface&MockObject $collection;

    /**
     * @var TreeInterface&MockObject
     */
    protected TreeInterface&MockObject $tree;

    /**
     * @var Treenode
     */
    protected Treenode $node;

    public function setUp(): void
    {
        $this->fixture = new TreenodeTestingFixture();
        $this->fixture->setUp();
        $this->collection = $this->createMock(CollectionInterface::class);
        $this->tree = $this->createMock(TreeInterface::class);
        $this->tree->method('getTreeId')->willReturn($this->fixture->treeId);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\node\Treenode::__construct
     */
    public function testConstruct(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        self::assertInstanceOf(TreenodeInterface::class, $node);
    }

    /**
     * testConstructFailsWhenCollectionIsNotEmpty
     *
     * @covers \pvc\struct\tree\node\Treenode::__construct
     */
    public function testConstructFailsWhenCollectionIsNotEmpty(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(false);
        self::expectException(ChildCollectionException::class);
        $node = new Treenode($this->collection);
        unset($node);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::hydrate
     */
    public function testHydrateFailsWhenNodeIsAlreadyHydrated(): void
    {
        $nodeId = 0;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);

        self::expectException(NodeNotEmptyHydrationException::class);
        $node->hydrate($dto);
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
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);

        $dto = $this->fixture->makeDTOUnordered($badNodeId, $parentId);
        self::expectException(InvalidNodeIdException::class);
        $node->hydrate($dto);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @throws ChildCollectionException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @throws InvalidValueException
     * @covers \pvc\struct\tree\node\Treenode::setParentId
     */
    public function testSetParentIdFailsWithInvalidParentId(): void
    {
        $nodeId = 5;
        $badParentId = -1;
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);

        $dto = $this->fixture->makeDTOUnordered($nodeId, $badParentId);
        self::expectException(InvalidParentNodeIdException::class);
        $node->hydrate($dto);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @throws ChildCollectionException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @throws InvalidValueException
     * @covers \pvc\struct\tree\node\Treenode::setTreeId
     */
    public function testSetTreeIdFailsWithInvalidTreeId(): void
    {
        $nodeId = 5;
        $parentId = 1;
        $treeId = -1;
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);

        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
        self::expectException(InvalidTreeidException::class);
        $node->hydrate($dto);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getIndex
     */
    public function testGetIndexReturnsNull(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        assertNull($node->getIndex());
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @throws ChildCollectionException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @throws InvalidValueException
     * @covers \pvc\struct\tree\node\Treenode::setTree
     */
    public function testSetTreeFailsWhenTreReferenceHasAlreadyBeenSet(): void
    {
        $nodeId = 0;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        $dto = new TreenodeDto($nodeId, $parentId, $this->fixture->treeId);
        $node->hydrate($dto);

        $node->setTree($this->tree);
        self::expectException(SetTreeException::class);
        $node->setTree($this->tree);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws InvalidTreeidException
     * @throws SetTreeException
     * @covers \pvc\struct\tree\node\Treenode::setTree
     */
    public function testSetTreeAdoptsTreeIdFromTreeIfTreeIdIsNotSet(): void
    {
        $nodeId = 0;
        $parentId = null;
        $treeId = null;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
        $node->hydrate($dto);
        $node->setTree($this->tree);
        self::assertEquals($this->tree->getTreeId(), $node->getTreeId());
    }

    /**
     * testHydrateFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree
     *
     * @covers \pvc\struct\tree\node\Treenode::setTree
     */
    public function testSetTreeFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree(
    ): void
    {
        $nodeId = 0;
        $parentId = null;

        /**
         * $this->tree->getTreeId returns $fixture->treeId, so to set up the mismatch,
         * add 1 to the treeId in the tree
         */

        $dtoTreeId = $this->fixture->treeId + 1;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        $dto = new TreenodeDto($nodeId, $parentId, $dtoTreeId);
        $node->hydrate($dto);

        self::expectException(SetTreeException::class);
        $node->setTree($this->tree);
    }

    /**
     * testSetParentFailsWithNonExistentNonNullParentId
     *
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWithNonExistentNonNullParentId(): void
    {
        $nodeId = 0;
        $parentId = 5;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);

        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);
        $node->setTree($this->tree);

        $this->tree->expects($this->once())->method('getNode')->with($parentId)
            ->willReturn(null);

        self::expectException(InvalidParentNodeIdException::class);
        $node->setParent($parentId);
    }

    /**
     * testSetParentSetsNullParent
     *
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentSetsNullParent(): void
    {
        $nodeId = 0;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);

        $this->tree->expects($this->once())->method('getRoot')->willReturn(
            null
        );
        $node->setTree($this->tree);
        $node->setParent($parentId);
    }

    /**
     * testSetParentFailsWhenCircularGraphCreated
     *
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWhenCircularGraphCreated(): void
    {
        $nodeId = 0;
        $parentId = 1;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);
        $node->setTree($this->tree);

        $parentNode = $this->createMock(TreenodeInterface::class);
        $parentNode->method('getNodeId')->willReturn($parentId);
        $parentNode->method('isDescendantOf')->with($node)->willReturn(true);

        $this->tree->expects($this->once())->method('getNode')->with($parentId)
            ->willReturn($parentNode);

        self::expectException(CircularGraphException::class);
        $node->setParent($parentId);
    }

    /**
     * testSetParentFailsIfNodeArgumentIsAlreadySetAsRoot
     *
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsIfNodeIsAlreadySetAsRoot(): void
    {
        $nodeId = 0;
        $parentId = null;
        $newParentId = 1;

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);
        $node->setTree($this->tree);

        $parentNode = $this->createMock(TreenodeInterface::class);
        $parentNode->method('getNodeId')->willReturn($newParentId);
        $parentNode->method('isDescendantOf')->with($node)->willReturn(false);

        $this->tree->expects($this->once())->method('getRoot')->willReturn(
            $node
        );
        $this->tree->expects($this->once())->method('getNode')->with(
            $newParentId
        )->willReturn($parentNode);
        self::expectException(RootCannotBeMovedException::class);

        $node->setParent($newParentId);
    }

    /**
     * testSetParentAddsNodeToParentsChildrenIfParentIsNotNull
     *
     * @covers \pvc\struct\tree\node\Treenode::setParent
     * @covers \pvc\struct\tree\node\Treenode::hydrate
     * @covers \pvc\struct\tree\node\Treenode::isEmpty
     * @covers \pvc\struct\tree\node\Treenode::getNodeId
     * @covers \pvc\struct\tree\node\Treenode::getParentId
     * @covers \pvc\struct\tree\node\Treenode::getParent
     * @covers \pvc\struct\tree\node\Treenode::getTreeId
     * @covers \pvc\struct\tree\node\Treenode::getTree
     * @covers \pvc\struct\tree\node\Treenode::getChildren
     */
    public function testSetParentAddsNodeToParentsChildrenIfParentIsNotNull(
    ): void
    {
        $nodeId = 0;
        $parentId = 1;

        $siblings = $this->createMock(CollectionInterface::class);
        $siblings->expects($this->once())->method('add');

        $mockRoot = $this->createMock(TreenodeInterface::class);
        $mockRoot->method('getNodeId')->willReturn($parentId);
        $mockRoot->method('getChildren')->willReturn($siblings);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $mockRoot) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $mockRoot,
            };
        };

        $this->tree->method('getRoot')->willReturn($mockRoot);
        $this->tree->method('getNode')->willReturnCallback($getNodeCallback);

        $this->collection->method('isEmpty')->willReturn(true);

        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);

        self::assertTrue($node->isEmpty());
        $node->hydrate($dto);
        self::assertFalse($node->isEmpty());
        $node->setTree($this->tree);
        $node->setParent($parentId);

        self::assertEquals($mockRoot, $node->getParent());

        /**
         * test all the getters
         */
        self::assertEquals($nodeId, $node->getNodeId());
        self::assertEquals($parentId, $node->getParentId());
        self::assertEquals($this->fixture->treeId, $node->getTreeId());
        self::assertEquals($this->tree, $node->getTree());
        self::assertEquals($this->fixture->grandChildren, $node->getChildren());
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getFirstChild
     */
    public function testGetFirstChild(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getFirst')
            ->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getFirstChild();
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getLastChild
     */
    public function testGetLastChild(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getLast')
            ->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getLastChild();
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getNthChild
     */
    public function testGetNthChild(): void
    {
        $n = 2;
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getNth')->with($n)
            ->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getNthChild($n);
    }

    /**
     * testGetChildrenAsArray
     *
     * @covers \pvc\struct\tree\node\Treenode::getChildrenArray
     */
    public function testGetChildrenAsArray(): void
    {
        $expectedResult = [$this->fixture->grandChild];
        self::assertEquals(
            $expectedResult,
            $this->fixture->child->getChildrenArray()
        );
    }

    /**
     * testIsLeafOnNodeWithNoChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::isLeaf
     */
    public function testIsLeafOnNodeWithNoChildren(): void
    {
        self::assertTrue($this->fixture->grandChild->isLeaf());
    }

    /**
     * testIsLeafOnNodeWithChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::isLeaf
     */
    public function testIsLeafOnNodeWithChildren(): void
    {
        self::assertFalse($this->fixture->root->isLeaf());
    }

    /**
     * testHasChildrenOnNodeWithNoChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenOnNodeWithNoChildren(): void
    {
        self::assertFalse($this->fixture->grandChild->hasChildren());
    }

    /**
     * testHasChildrenNodeOnNodeWithChildren
     *
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenNodeOnNodeWithChildren(): void
    {
        self::assertTrue($this->fixture->root->hasChildren());
    }

    /**
     * testGetChild
     *
     * @covers \pvc\struct\tree\node\Treenode::getChild
     */
    public function testGetChild(): void
    {
        self::assertEquals(
            $this->fixture->child->getChild($this->fixture->grandChildNodeId),
            $this->fixture->root->getChild($this->fixture->childNodeId)
                ->getChild($this->fixture->grandChildNodeId)
        );
        /**
         * get child returns null if nodeId does not exist in the child collection
         */
        $nonExistentChildId = 9;
        self::assertNull($this->fixture->root->getChild($nonExistentChildId));
    }

    /**
     * testGetSiblingsForTreeWithRootOnly
     * logic for getting siblings for the root is different from any other node because the root has no parent.
     *
     * @covers \pvc\struct\tree\node\Treenode::getSiblings
     */
    public function testGetSiblingsForTreeWithRootOnly(): void
    {
        /**
         * the only time the tree is called upon to get a collection factory is in the course of a request from the root
         * node when it tries to get its siblings.  The test verifies that the node goes to the tree to get a
         * TreenodeFactory and the TreenodeFactory goes to the CollectionFactory and the CollectionFactory gets
         * called on to make a collection
         */

        $this->fixture->mockTree->method('makeCollection')->willReturn(
            $this->fixture->rootSiblingsCollection
        );
        $siblings = $this->fixture->root->getSiblings();
        self::assertInstanceOf(CollectionInterface::class, $siblings);
    }

    /**
     * testGetSiblingsForChildrenOfRoot
     *
     * @covers \pvc\struct\tree\node\Treenode::getSiblings
     */
    public function testGetSiblingsForChildrenOfRoot(): void
    {
        self::assertEquals(
            $this->fixture->children,
            $this->fixture->child->getSiblings()
        );
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\Treenode::isRoot
     */
    public function testIsRoot(): void
    {
        self::assertTrue($this->fixture->root->isRoot());
        self::assertFalse($this->fixture->child->isRoot());
    }

    /**
     * testIsDescendantOf
     *
     * @covers \pvc\struct\tree\node\Treenode::isDescendantOf
     */
    public function testIsDescendantOf(): void
    {
        self::assertTrue(
            $this->fixture->child->isDescendantOf($this->fixture->root)
        );
        self::assertFalse(
            $this->fixture->child->isDescendantOf($this->fixture->grandChild)
        );
    }

    /**
     * testIsAncestorof
     *
     * @covers \pvc\struct\tree\node\Treenode::isAncestorOf
     */
    public function testIsAncestorof(): void
    {
        self::assertTrue(
            $this->fixture->child->isAncestorOf($this->fixture->grandChild)
        );
        self::assertFalse(
            $this->fixture->child->isAncestorOf($this->fixture->root)
        );
    }

    protected function getRoot(): mixed
    {
        return $this->fixture;
    }
}
