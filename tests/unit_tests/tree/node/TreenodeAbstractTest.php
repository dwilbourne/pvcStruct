<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\collection\CollectionAbstract;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidValueException;
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

    public function setUp(): void
    {
        $this->fixture = $this->getMockForAbstractClass(TreenodeTestingFixtureAbstract::class);
    }

    /**
     * testConstructFailsWithInvalidNodeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     */
    public function testConstructFailsWithInvalidNodeid(): void
    {
        $treeId = 0;
        $nodeId = -2;
        $parentId = null;
        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);

        self::expectException(InvalidNodeIdException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
    }

    /**
     * testConstructFailsWhenNodeWithSameNodeidAlreadyExistsInTree
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     * @throws InvalidNodeIdException
     */
    public function testConstructFailsWhenNodeWithSameNodeidAlreadyExistsInTree(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = null;
        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockDuplicate = $this->createMock(TreenodeAbstractInterface::class);
        $mockTree->method('getNode')->with($nodeId)->willReturn($mockDuplicate);

        self::expectException(AlreadySetNodeidException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
        unset($node);
    }

    /**
     * testConstructFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     * @throws InvalidNodeIdException
     */
    public function testConstructFailsWhenTreeIdDoesNotMatchTreeIdOfContainingTree(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = null;
        $nonMatchingTreeId = 1;
        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);

        self::expectException(SetTreeIdException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $nonMatchingTreeId, $mockTree, $mockCollection);
        unset($node);
    }

    /**
     * testConstructFailsWhenChildCollectionIsNotEmpty
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     * @throws InvalidNodeIdException
     */
    public function testConstructFailsWhenChildCollectionIsNotEmpty(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = null;
        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        $mockTree->method('getNode')->with($nodeId)->willReturn(null);
        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(false);

        self::expectException(ChildCollectionException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
        unset($node);
    }

    /**
     * testConstructSucceeds
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
     */
    public function testConstructSucceeds(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = null;
        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        $mockTree->method('getNode')->with($nodeId)->willReturn(null);
        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);
        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
        self::assertInstanceOf(TreenodeAbstractInterface::class, $node);
    }

    /**
     * testSetParentFailsWithBadNonNullParentId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     * @throws InvalidNodeIdException
     */
    public function testSetParentFailsWithBadNonNullParentId(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $badParentId = 10;

        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        /**
         * no node in the tree with node id or with bad parent id
         */
        $mockTree->method('getNode')->willReturn(null);

        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);

        self::expectException(InvalidParentNodeException::class);

        $node = new TreenodeAbstract($nodeId, $badParentId, $treeId, $mockTree, $mockCollection);
        unset($node);
    }

    /**
     * testSetParentFailsWhenCircularGraphCreated
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentFailsWhenCircularGraphCreated(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = 1;

        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);

        $mockParent = $this->createMock(TreenodeAbstractInterface::class);
        $mockParent->method('isDescendantOf')->willReturn(true);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $mockParent) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $mockParent,
            };
        };

        $mockTree->method('getNode')->willReturnCallback($getNodeCallback);

        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);

        self::expectException(CircularGraphException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
    }

    /**
     * testSetParentFailsIfNodeArgumentIsAlreadySetAsRoot
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentFailsIfNodeIsAlreadySetAsRoot(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = 1;

        $mockParent = $this->createMock(TreenodeAbstractInterface::class);

        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);

        $mockRoot = $this->createMock(TreenodeAbstractInterface::class);
        $mockRoot->method('getNodeId')->willReturn($nodeId);

        $getNodeCallback = function ($arg) use ($nodeId, $parentId, $mockParent) {
            return match ($arg) {
                $nodeId => null,
                $parentId => $mockParent,
            };
        };

        $mockTree->method('getRoot')->willReturn($mockRoot);
        $mockTree->method('getNode')->willReturnCallback($getNodeCallback);

        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);

        self::expectException(RootCannotBeMovedException::class);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
    }

    /**
     * testSetParentAddsNodeToParentsChildrenIfParentIsNotNull
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
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

        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        $mockTree->method('getRoot')->willReturn($mockRoot);
        $mockTree->method('getNode')->willReturnCallback($getNodeCallback);

        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);
        self::assertEquals($mockRoot, $node->getParent());
    }

    /**
     * testConstructSetsNullParent
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParent
     */
    public function testSetParentSetsNullParent(): void
    {
        $treeId = 0;
        $nodeId = 0;
        $parentId = null;

        $mockTree = $this->createMock(TreeAbstractInterface::class);
        $mockTree->method('getTreeId')->willReturn($treeId);
        $mockTree->method('getNode')->with($nodeId)->willReturn(null);

        $mockCollection = $this->getMockForAbstractClass(CollectionAbstractInterface::class);
        $mockCollection->method('isEmpty')->willReturn(true);

        $node = new TreenodeAbstract($nodeId, $parentId, $treeId, $mockTree, $mockCollection);

        self::assertNull($node->getParent());
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
     * testSetGetValueValidator
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setPayloadValidator
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getPayloadValidator
     */
    public function testSetGetValueValidator(): void
    {
        $this->createTree();
        $validator = $this->createStub(ValidatorPayloadInterface::class);

        $this->getRoot()->setPayloadValidator($validator);
        self::assertEquals($validator, $this->getRoot()->getPayloadValidator());
    }

    /**
     * testSetValueFailsWhenValidatorDeterminesInvalidValue
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setPayload
     * @throws InvalidNodeIdException
     * @throws InvalidValueException
     */
    public function testSetValueFailsWhenValidatorDeterminesInvalidValue(): void
    {
        $this->createTree();

        $validator = $this->createStub(ValidatorPayloadInterface::class);
        $validator->method('validate')->willReturn(false);
        $this->getRoot()->setPayloadValidator($validator);

        self::expectException(InvalidValueException::class);
        $this->getRoot()->setPayload('foo');
    }

    /**
     * testSetGetValue
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setPayload
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getPayload
     *
     */
    public function testSetGetValue(): void
    {
        $this->createTree();
        $value = 'foobar';
        $this->getRoot()->setPayload($value);
        self::assertEquals($value, $this->getRoot()->getPayload());
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
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getVisitCount
     * @covers \pvc\struct\tree\node\TreenodeAbstract::addVisit
     * @covers \pvc\struct\tree\node\TreenodeAbstract::clearVisitCount
     *
     */
    public function testAddGetClearVisitCount(): void
    {
        $this->createTree();

        self::assertEquals(0, $this->getRoot()->getVisitCount());
        $this->getRoot()->addVisit();
        self::assertEquals(1, $this->getRoot()->getVisitCount());
        $this->getRoot()->clearVisitCount();
        self::assertEquals(0, $this->getRoot()->getVisitCount());
    }
}
