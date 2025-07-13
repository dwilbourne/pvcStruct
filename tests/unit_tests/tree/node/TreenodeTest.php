<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\testingutils\testingTraits\IteratorTrait;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixture;

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
     * @covers \pvc\struct\tree\node\Treenode::__construct
     */
    public function testConstruct(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        self::assertInstanceOf(TreenodeInterface::class, $node);
    }

    /**
     * testConstructFailsWhenCollectionIsNotEmpty
     * @covers \pvc\struct\tree\node\Treenode::__construct
     */
    public function testConstructFailsWhenCollectionIsNotEmpty(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(false);
        self::expectException(ChildCollectionException::class);
        $node = new Treenode($this->collection, $this->tree);
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

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        $node->hydrate($dto);

        self::expectException(NodeNotEmptyHydrationException::class);
        $node->hydrate($dto);
    }

    /**
     * testSetNodeIdFailsWithInvalidNodeId
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\Treenode::setNodeId
     */
    public function testSetNodeIdFailsWithInvalidNodeId(): void
    {
        $badNodeId = -2;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);

        $dto = $this->fixture->makeDTOUnordered($badNodeId, $parentId);
        self::expectException(InvalidNodeIdException::class);
        $node->hydrate($dto);
    }

    /**
     * testSetNodeIdFailsWhenNodeWithSameNodeIdAlreadyExistsInTree
     * @covers \pvc\struct\tree\node\Treenode::setNodeId
     * @throws InvalidNodeIdException
     */
    public function testSetNodeIdFailsWhenNodeWithSameNodeIdAlreadyExistsInTree(): void
    {
        $nodeId = 0;
        $parentId = null;

        $mockDuplicate = $this->createMock(Treenode::class);
        $this->tree->expects($this->once())->method('getNode')->with($nodeId)->willReturn($mockDuplicate);

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);

        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);
        self::expectException(AlreadySetNodeidException::class);
        $node->hydrate($dto);
    }

    /**
     * testHydrateFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree
     * @covers \pvc\struct\tree\node\Treenode::hydrate
     */
    public function testHydrateFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree(): void
    {
        $nodeId = 0;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDtoWithNonMatchingTreeId($nodeId, $parentId);

        $this->tree->expects($this->once())->method('getNode')->with($nodeId)->willReturn(null);

        self::expectException(SetTreeIdException::class);

        $node->hydrate($dto);
    }

    /**
     * testSetParentFailsWithNonExistentNonNullParentId
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWithNonExistentNonNullParentId(): void
    {
        $nodeId = 0;
        $parentId = 5;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturn(null);

        self::expectException(InvalidParentNodeException::class);
        $node->hydrate($dto);
    }

    /**
     * testSetParentSetsNullParent
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentSetsNullParent(): void
    {
        $nodeId = 0;
        $parentId = null;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);

        $this->tree->expects($this->exactly(1))->method('getNode')->willReturn(null);
        $this->tree->expects($this->once())->method('getRoot')->willReturn(null);

        $node->hydrate($dto);
    }

    /**
     * testSetParentFailsWhenCircularGraphCreated
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsWhenCircularGraphCreated(): void
    {
        $nodeId = 0;
        $parentId = 1;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);

        $parentNode = $this->createMock(TreenodeInterface::class);
        $parentNode->method('getNodeId')->willReturn($parentId);
        $parentNode->method('isDescendantOf')->with($node)->willReturn(true);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $parentNode) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $parentNode,
            };
        };

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturnCallback($getNodeCallback);
        self::expectException(CircularGraphException::class);
        $node->hydrate($dto);
    }

    /**
     * testSetParentFailsIfNodeArgumentIsAlreadySetAsRoot
     * @covers \pvc\struct\tree\node\Treenode::setParent
     */
    public function testSetParentFailsIfNodeIsAlreadySetAsRoot(): void
    {
        $nodeId = 0;
        $parentId = 1;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new Treenode($this->collection, $this->tree);
        $dto = $this->fixture->makeDTOUnordered($nodeId, $parentId);

        $parentNode = $this->createMock(TreenodeInterface::class);
        $parentNode->method('getNodeId')->willReturn($parentId);
        $parentNode->method('isDescendantOf')->with($node)->willReturn(false);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $parentNode) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $parentNode,
            };
        };

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturnCallback($getNodeCallback);
        $this->tree->expects($this->once())->method('getRoot')->willReturn($node);
        self::expectException(RootCannotBeMovedException::class);

        $node->hydrate($dto);
    }

    /**
     * testSetParentAddsNodeToParentsChildrenIfParentIsNotNull
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
    public function testSetParentAddsNodeToParentsChildrenIfParentIsNotNull(): void
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
        self::assertEquals($mockRoot, $node->getParent());

        /**
         * test all the getters
         */
        self::assertEquals($nodeId, $node->getNodeId());
        self::assertEquals($parentId, $node->getParentId());
        self::assertEquals($mockRoot, $node->getParent());
        self::assertEquals($this->fixture->treeId, $node->getTreeId());
        self::assertEquals($this->tree, $node->getTree());
        self::assertEquals($this->fixture->grandChildren, $node->getChildren());
    }

    protected function getRoot(): mixed
    {
        return $this->fixture;
    }

    /**
     * @return void
     * @throws \pvc\struct\tree\err\ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getFirstChild
     */
    public function testGetFirstChild(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getFirst')->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getFirstChild();
    }

    /**
     * @return void
     * @throws \pvc\struct\tree\err\ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getLastChild
     */
    public function testGetLastChild(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getLast')->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getLastChild();
    }

    /**
     * @return void
     * @throws \pvc\struct\tree\err\ChildCollectionException
     * @covers \pvc\struct\tree\node\Treenode::getNthChild
     */
    public function testGetNthChild(): void
    {
        $n  = 2;
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $mockChild = $this->createMock(Treenode::class);
        $this->collection->expects($this->once())->method('getNth')->with($n)->willReturn($mockChild);
        $node = new Treenode($this->collection, $this->tree);
        $node->getNthChild($n);
    }


    /**
     * testGetChildrenAsArray
     * @covers \pvc\struct\tree\node\Treenode::getChildrenArray
     */
    public function testGetChildrenAsArray(): void
    {
        $expectedResult = [$this->fixture->grandChild];
        self::assertEquals($expectedResult, $this->fixture->child->getChildrenArray());
    }

    /**
     * testIsLeafOnNodeWithNoChildren
     * @covers \pvc\struct\tree\node\Treenode::isLeaf
     */
    public function testIsLeafOnNodeWithNoChildren(): void
    {
        self::assertTrue($this->fixture->grandChild->isLeaf());
    }

    /**
     * testIsLeafOnNodeWithChildren
     * @covers \pvc\struct\tree\node\Treenode::isLeaf
     */
    public function testIsLeafOnNodeWithChildren(): void
    {
        self::assertFalse($this->fixture->root->isLeaf());
    }

    /**
     * testHasChildrenOnNodeWithNoChildren
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenOnNodeWithNoChildren(): void
    {
        self::assertFalse($this->fixture->grandChild->hasChildren());
    }

    /**
     * testHasChildrenNodeOnNodeWithChildren
     * @covers \pvc\struct\tree\node\Treenode::hasChildren
     */
    public function testHasChildrenNodeOnNodeWithChildren(): void
    {
        self::assertTrue($this->fixture->root->hasChildren());
    }

    /**
     * testGetChild
     * @covers \pvc\struct\tree\node\Treenode::getChild
     */
    public function testGetChild(): void
    {
        self::assertEquals(
            $this->fixture->child->getChild($this->fixture->grandChildNodeId),
            $this->fixture->root->getChild($this->fixture->childNodeId)->getChild($this->fixture->grandChildNodeId)
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

        $mockTreenodeCollectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $mockTreenodeCollectionFactory->expects($this->once())->method('makeCollection')->willReturn($this->fixture->rootSiblingsCollection);

        $this->fixture->mockTree->method('getCollectionFactory')->willReturn($mockTreenodeCollectionFactory);

        $siblings = $this->fixture->root->getSiblings();
        self::assertInstanceOf(CollectionInterface::class, $siblings);
    }

    /**
     * testGetSiblingsForChildrenOfRoot
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
     * @covers \pvc\struct\tree\node\Treenode::isDescendantOf
     */
    public function testIsDescendantOf(): void
    {
        self::assertTrue($this->fixture->child->isDescendantOf($this->fixture->root));
        self::assertFalse($this->fixture->child->isDescendantOf($this->fixture->grandChild));
    }

    /**
     * testIsAncestorof
     * @covers \pvc\struct\tree\node\Treenode::isAncestorOf
     */
    public function testIsAncestorof(): void
    {
        self::assertTrue($this->fixture->child->isAncestorOf($this->fixture->grandChild));
        self::assertFalse($this->fixture->child->isAncestorOf($this->fixture->root));
    }
}
