<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\collection\CollectionAbstract;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidVisitStatusException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\node\TreenodeAbstract;
use pvc\struct\tree\tree\TreeAbstract;
use pvc\testingutils\testingTraits\IteratorTrait;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixtureAbstract;

class TreenodeAbstractTest extends TestCase
{
    use IteratorTrait;

    protected TreenodeTestingFixtureAbstract $fixture;

    /**
     * @var CollectionAbstractInterface|MockObject
     */
    protected CollectionAbstractInterface $collection;

    /**
     * @var PayloadTesterInterface|MockObject
     */
    protected PayloadTesterInterface $tester;

    /**
     * @var TreenodeValueObjectInterface|MockObject
     */
    protected TreenodeValueObjectInterface $valueObject;

    /**
     * @var TreeAbstractInterface|MockObject
     */
    protected TreeAbstractInterface $tree;

    /**
     * @var TreenodeAbstract|MockObject
     */
    protected TreenodeAbstract $node;

    public function setUp(): void
    {
        $this->fixture = $this->getMockForAbstractClass(TreenodeTestingFixtureAbstract::class);
        $this->collection = $this->createMock(CollectionAbstractInterface::class);
        $this->tester = $this->createMock(PayloadTesterInterface::class);
        $this->valueObject = $this->createMock(TreenodeValueObjectInterface::class);
        $this->tree = $this->createMock(TreeAbstractInterface::class);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     */
    public function testConstruct(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();
        self::assertInstanceOf(TreenodeAbstractInterface::class, $this->node);
    }

    /**
     * testConstructFailsWhenCollectionIsNotEmpty
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     */
    public function testConstructFailsWhenCollectionIsNotEmpty(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(false);
        self::expectException(ChildCollectionException::class);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();
    }

    /**
     * testHydrateFailsWithInvalidNodeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::hydrate
     */
    public function testHydrateFailsWithInvalidNodeid(): void
    {
        $nodeId = -2;
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();
        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        self::expectException(InvalidNodeIdException::class);
        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testHydrateFailsWhenNodeWithSameNodeidAlreadyExistsInTree
     * @covers \pvc\struct\tree\node\TreenodeAbstract::hydrate
     * @throws InvalidNodeIdException
     */
    public function testHydrateFailsWhenNodeWithSameNodeidAlreadyExistsInTree(): void
    {
        $nodeId = 1;
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();
        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $mockDuplicate = $this->createMock(TreenodeAbstractInterface::class);
        $this->tree->expects($this->once())->method('getNode')->with($nodeId)->willReturn($mockDuplicate);
        self::expectException(AlreadySetNodeidException::class);
        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testHydrateFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree
     * @covers \pvc\struct\tree\node\TreenodeAbstract::hydrate
     */
    public function testHydrateFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree(): void
    {
        $nodeId = 1;
        $valueObjectTreeId = 0;
        $treeId = 3;
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getTreeId')->willReturn($valueObjectTreeId);

        $this->tree->expects($this->once())->method('getNode')->with($nodeId)->willReturn(null);
        $this->tree->expects($this->once())->method('getTreeId')->willReturn($treeId);

        self::expectException(SetTreeIdException::class);

        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testSetParentFailsWithNonExistentNonNullParentId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentFailsWithNonExistentNonNullParentId(): void
    {
        $nodeId = 1;
        $parentId = 0;
        $treeId = 0;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getParentId')->willReturn($parentId);
        $this->valueObject->method('getTreeId')->willReturn($treeId);

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturn(null);
        $this->tree->expects($this->once())->method('getTreeId')->willReturn($treeId);

        self::expectException(InvalidParentNodeException::class);

        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testSetParentSetsNullParent
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentSetsNullParent(): void
    {
        $nodeId = 0;
        $parentId = null;
        $treeId = 0;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->tester->method('testValue')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getParentId')->willReturn($parentId);
        $this->valueObject->method('getTreeId')->willReturn($treeId);
        $this->valueObject->method('getPayload')->willReturn(null);

        $this->tree->expects($this->exactly(1))->method('getNode')->willReturn(null);
        $this->tree->expects($this->once())->method('getTreeId')->willReturn($treeId);
        $this->tree->expects($this->once())->method('getRoot')->willReturn(null);

        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testSetParentFailsWhenCircularGraphCreated
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentFailsWhenCircularGraphCreated(): void
    {
        $nodeId = 1;
        $parentId = 0;
        $treeId = 0;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getParentId')->willReturn($parentId);
        $this->valueObject->method('getTreeId')->willReturn($treeId);

        $parentNode = $this->createMock(TreenodeAbstractInterface::class);
        $parentNode->method('getNodeId')->willReturn($parentId);
        $parentNode->method('isDescendantOf')->with($this->node)->willReturn(true);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $parentNode) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $parentNode,
            };
        };

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturnCallback($getNodeCallback);
        $this->tree->expects($this->once())->method('getTreeId')->willReturn($treeId);

        self::expectException(CircularGraphException::class);

        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testSetParentFailsIfNodeArgumentIsAlreadySetAsRoot
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentFailsIfNodeIsAlreadySetAsRoot(): void
    {
        $nodeId = 1;
        $parentId = 0;
        $treeId = 0;

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getParentId')->willReturn($parentId);
        $this->valueObject->method('getTreeId')->willReturn($treeId);

        $parentNode = $this->createMock(TreenodeAbstractInterface::class);
        $parentNode->method('getNodeId')->willReturn($parentId);
        $parentNode->method('isDescendantOf')->with($this->node)->willReturn(false);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $parentNode) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $parentNode,
            };
        };

        $this->tree->expects($this->exactly(2))->method('getNode')->willReturnCallback($getNodeCallback);
        $this->tree->expects($this->once())->method('getTreeId')->willReturn($treeId);
        $this->tree->expects($this->once())->method('getRoot')->willReturn($this->node);

        self::expectException(RootCannotBeMovedException::class);

        $this->node->hydrate($this->valueObject, $this->tree);
    }

    /**
     * testSetParentAddsNodeToParentsChildrenIfParentIsNotNull
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     * @covers \pvc\struct\tree\node\TreenodeAbstract::hydrate
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isEmpty
     */
    public function testSetParentAddsNodeToParentsChildrenIfParentIsNotNull(): void
    {

        $treeId = 0;
        $parentId = 0;
        $nodeId = 1;

        $siblings = $this->createMock(CollectionAbstractInterface::class);
        $siblings->expects($this->once())->method('push');

        $mockRoot = $this->createMock(TreenodeAbstractInterface::class);
        $mockRoot->method('getNodeId')->willReturn($parentId);
        $mockRoot->method('getChildren')->willReturn($siblings);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $mockRoot) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $mockRoot,
            };
        };

        $this->tree->method('getTreeId')->willReturn($treeId);
        $this->tree->method('getRoot')->willReturn($mockRoot);
        $this->tree->method('getNode')->willReturnCallback($getNodeCallback);

        $this->collection->method('isEmpty')->willReturn(true);

        $this->valueObject->method('getNodeId')->willReturn($nodeId);
        $this->valueObject->method('getParentId')->willReturn($parentId);
        $this->valueObject->method('getTreeId')->willReturn($treeId);
        $this->valueObject->method('getPayload')->willReturn(null);

        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
                           ->setConstructorArgs([$this->collection, $this->tester])
                           ->getMockForAbstractClass();
        $this->tester->method('testValue')->willReturn(true);

        self::assertTrue($this->node->isEmpty());
        $this->node->hydrate($this->valueObject, $this->tree);
        self::assertFalse($this->node->isEmpty());
        self::assertEquals($mockRoot, $this->node->getParent());

        /**
         * verify that you cannot hydrate a node which is not empty
         */
        self::expectException(NodeNotEmptyHydrationException::class);
        $this->node->hydrate($this->valueObject, $this->tree);
    }


    protected function createTree(): void
    {
        $this->fixture->createMockTree(
            CollectionAbstract::class,
            TreeAbstract::class
        );
    }

    protected function getRoot(): mixed
    {
        return $this->fixture->getRoot();
    }


    /**
     * testConstructSetsCorePropertiesCorrectly
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getNodeId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getParent
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getParentId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getTree
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getTreeId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getChildren
     */
    public function testConstructSetsCorePropertiesCorrectly(): void
    {
        $this->createTree();
        self::assertEquals($this->getChildNodeId(), $this->getChild()->getNodeId());
        self::assertEquals($this->getRoot(), $this->getChild()->getParent());
        self::assertEquals($this->getRoot()->getNodeId(), $this->getChild()->getParentId());
        self::assertEquals($this->getMockTree(), $this->getChild()->getTree());
        self::assertEquals($this->getMockTree()->getTreeId(), $this->getChild()->getTreeId());
        self::assertInstanceOf(CollectionAbstractInterface::class, $this->getChild()->getChildren());
    }

    protected function getChildNodeId(): int
    {
        return $this->fixture->getChildNodeId();
    }

    protected function getChild(): mixed
    {
        return $this->fixture->getChild();
    }

    protected function getMockTree(): mixed
    {
        return $this->fixture->getMockTree();
    }

    protected function getTreeId(): int
    {
        return $this->fixture->getTreeId();
    }

    /**
     * testIsLeafOnNodeWithNoChildren
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isLeaf
     */
    public function testIsLeafOnNodeWithNoChildren(): void
    {
        $this->createTree();
        self::assertTrue($this->getGrandChild()->isLeaf());
    }

    /**
     * construction and all the setters and getters are tested at this point.
     */

    protected function getGrandChild(): mixed
    {
        return $this->fixture->getGrandChild();
    }

    /**
     * testIsLeafOnNodeWithChildren
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isLeaf
     */
    public function testIsLeafOnNodeWithChildren(): void
    {
        $this->createTree();
        self::assertFalse($this->getRoot()->isLeaf());
    }

    /**
     * testIsInteriorNodeOnNodeWithNoChildren
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isInteriorNode
     */
    public function testIsInteriorNodeOnNodeWithNoChildren(): void
    {
        $this->createTree();
        self::assertFalse($this->getGrandChild()->isInteriorNode());
    }

    /**
     * testIsInteriorNodeOnRootWithChildren
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isInteriorNode
     */
    public function testIsInteriorNodeOnNodeWithChildren(): void
    {
        $this->createTree();
        self::assertTrue($this->getRoot()->isInteriorNode());
    }

    /**
     * testGetChild
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getChild
     */
    public function testGetChild(): void
    {
        $this->createTree();
        self::assertEquals($this->getChild(), $this->getRoot()->getChild($this->getChild()->getNodeId()));
        self::assertNull($this->getRoot()->getChild($this->getRoot()->getNodeId()));
    }

    /**
     * testGetSiblingsForTreeWithRootOnly
     * logic for getting siblings for the root is different from any other node because the root has no parent.
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getSiblings
     *
     */
    public function testGetSiblingsForTreeWithRootOnly(): void
    {
        $this->createTree();
        $siblings = $this->getRoot()->getSiblings();
        self::assertInstanceOf(CollectionAbstractInterface::class, $siblings);
        self::assertEquals(1, count($siblings));
        self::assertEquals($siblings->current(), $this->getMockTree()->getRoot());
    }

    /**
     * testGetSiblingsForChildrenOfRoot
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getSiblings
     */
    public function testGetSiblingsForChildrenOfRoot(): void
    {
        $this->createTree();
        self::assertEquals($this->fixture->getChildren(), $this->getChild()->getSiblings());
    }

    /**
     * testIsDescendantOf
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isDescendantOf
     */
    public function testIsDescendantOf(): void
    {
        $this->createTree();
        self::assertTrue($this->getChild()->isDescendantOf($this->getRoot()));
        self::assertFalse($this->getChild()->isDescendantOf($this->getGrandChild()));
    }

    /**
     * testIsAncestorof
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isAncestorOf
     */
    public function testIsAncestorof(): void
    {
        $this->createTree();
        self::assertTrue($this->getChild()->isAncestorOf($this->getGrandChild()));
        self::assertFalse($this->getChild()->isAncestorOf($this->getRoot()));
    }

    /**
     * testAddGetClearVisitCount
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getVisitStatus
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setVisitStatus
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isValidVisitStatus
     */
    public function testSetGetVisitStatus(): void
    {
        $this->createTree();
        self::assertEquals(TreenodeAbstract::NEVER_VISITED, $this->getRoot()->getVisitStatus());
        $this->getRoot()->setVisitStatus(TreenodeAbstract::FULLY_VISITED);
        self::assertEquals(TreenodeAbstract::FULLY_VISITED, $this->getRoot()->getVisitStatus());
    }

    /**
     * testSetVisitStatusThrowsExceptionWithBadArgument
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setVisitStatus
     * @covers \pvc\struct\tree\node\TreenodeAbstract::isValidVisitStatus
     */
    public function testSetVisitStatusThrowsExceptionWithBadArgument(): void
    {
        $this->createTree();
        self::expectException(InvalidVisitStatusException::class);
        $this->getRoot()->setVisitStatus(9);
    }
}
