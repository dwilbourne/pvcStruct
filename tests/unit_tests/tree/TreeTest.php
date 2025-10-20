<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\err\NonExistentKeyException;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;
use pvc\struct\tree\Tree;

/**
 * Class TreeTest
 * @template NodeId of array-key
 * @template NodeType of TreenodeInterface
 * @template CollectionType of TreenodeCollectionInterface
 */
class TreeTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var ValTesterInterface<mixed>
     */
    protected ValTesterInterface $treeIdTester;

    /**
     * @var TreeInterface<non-negative-int, non-negative-int, NodeType>
     */
    protected TreeInterface $tree;

    /**
     * @var TreenodeFactoryInterface<NodeType>&MockObject
     */
    protected TreenodeFactoryInterface&MockObject $treenodeFactory;

    /**
     * @var TreenodeCollectionFactoryInterface<CollectionType>&MockObject
     */
    protected TreenodeCollectionFactoryInterface&MockObject $collectionFactory;

    /**
     * @var TreenodeCollectionInterface<NodeId, NodeType>&MockObject
     */
    protected TreenodeCollectionInterface $treenodeCollection;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->treeId = 0;

        $this->treenodeCollection = $this->createMock(TreenodeCollectionInterface::class);

        $this->collectionFactory = $this->createMock(TreenodeCollectionFactoryInterface::class);
        $this->collectionFactory->method('makeCollection')->willReturn($this->treenodeCollection);

        $this->treenodeFactory = $this->createMock(
            TreenodeFactoryInterface::class
        );

        $this->treeIdTester = $this->createMock(ValTesterInterface::class);

        /** @var Tree<non-negative-int, non-negative-int, NodeType> $tree */
        $tree = new Tree($this->treeIdTester, $this->treenodeFactory, $this->collectionFactory);
        $this->tree = $tree;
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\Tree::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(Tree::class, $this->tree);
    }

    /**
     * testSetInvalidTreeidThrowsException
     *
     * @throws Exception
     * @covers \pvc\struct\tree\Tree::setTreeId
     * @covers \pvc\struct\tree\Tree::validateTreeId
     */
    public function testSetInvalidTreeidThrowsException(): void
    {
        $invalidTreeId = -2;
        $this->treeIdTester->method('testValue')->willReturn(false);

        self::expectException(InvalidTreeidException::class);
        $this->tree->initialize($invalidTreeId);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\Tree::initialize
     * @covers \pvc\struct\tree\Tree::setTreeId
     */
    public function testInitialize(): void
    {
        $this->collectionFactory->expects($this->once())->method('makeCollection');
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);
        self::assertTrue(is_null($this->tree->getRoot()));
    }

    /**
     * testRootTest
     *
     * @covers \pvc\struct\tree\Tree::rootTest
     */
    public function testRootTestOnDTO(): void
    {
        $parentId = null;

        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getParentId')->willReturn($parentId);
        self::assertTrue($this->tree->rootTest($dto));

        $parentId = 1;
        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getParentId')->willReturn($parentId);
        self::assertFalse($this->tree->rootTest($dto));
    }

    /**
     * testRootTestOnNode
     *
     * @covers \pvc\struct\tree\Tree::rootTest
     */
    public function testRootTestOnNode(): void
    {
        /** @var TreenodeInterface&MockObject $node */
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getParent')->willReturnOnConsecutiveCalls(null, $node);

        self::assertTrue($this->tree->rootTest($node));
        self::assertFalse($this->tree->rootTest($node));
    }

    /**
     * testWhenTreeHasNoNodes
     *
     * @covers \pvc\struct\tree\Tree::isEmpty
     * @covers \pvc\struct\tree\Tree::getNodeCollection
     * @covers \pvc\struct\tree\Tree::getNode
     * @covers \pvc\struct\tree\Tree::getRoot
     */
    public function testWhenTreeHasNoNodes(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $this->treenodeCollection->method('isEmpty')->willReturn(true);
        self::assertTrue($this->tree->isEmpty());
        self::assertNull($this->tree->getRoot());

        $nonExistentNodeId = 5;
        $exception = new NonExistentKeyException((string) $nonExistentNodeId);

        $this->treenodeCollection
            ->method('getElement')
            ->with($nonExistentNodeId)
            ->willReturn($this->throwException($exception));

        self::assertNull($this->tree->getNode($nonExistentNodeId));
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\Tree::addNode
     * @covers \pvc\struct\tree\Tree::setRoot
     * @covers \pvc\struct\tree\Tree::getRoot
     * @covers \pvc\struct\tree\Tree::getNodeCollection
     */
    public function testAdd(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->expects($this->once())->method('setParent')->with(null);
        $node->method('getParent')->willReturn(null);

        $this->treenodeCollection->expects($this->exactly(2))->method('getElement')->with($nodeId)->willReturnOnConsecutiveCalls(null, $node);
        $this->treenodeCollection->expects($this->once())->method('add')->with($node, $nodeId);

        $this->tree->addNode($node, null);

        self::assertSame($node, $this->tree->getRoot());
        self::assertSame($this->treenodeCollection, $this->tree->getNodeCollection());

        /**
         * adding it a second time produces an exception
         */
        self::expectException(AlreadySetNodeidException::class);
        $this->tree->addNode($node, null);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\Tree::setRoot
     */
    public function testAddingRootASecondTimeFails(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $nodeAId = 1;
        $nodeA = $this->createMock(TreenodeInterface::class);
        $nodeA->method('getNodeId')->willReturn($nodeAId);
        $nodeA->expects($this->once())->method('setParent')->with(null);
        $nodeA->method('getParent')->willReturn(null);

        $nodeBId = 2;
        $nodeB = $this->createMock(TreenodeInterface::class);
        $nodeB->method('getNodeId')->willReturn($nodeBId);
        $nodeB->expects($this->once())->method('setParent')->with(null);
        $nodeB->method('getParent')->willReturn(null);


        $this->treenodeCollection->expects($this->exactly(2))->method('getElement')->willReturn(null);

        $this->tree->addNode($nodeA, null);

        /**
         * adding $nodeB produces an exception
         */
        self::expectException(AlreadySetRootException::class);
        $this->tree->addNode($nodeB, null);

    }

    /**
     * @return void
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\Tree::deleteNode
     */
    public function testDeleteFailsWhenNodeIsNotInTree(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $nodeId = 1;
        $this->treenodeCollection->expects($this->once())->method('getElement')->with($nodeId)->willReturn(null);
        self::expectException(NodeNotInTreeException::class);

        $this->tree->initialize($this->treeId);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * @return void
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\Tree::deleteNode
     */
    public function testDeleteFailsWhenDeleteBranchIsFalseAndNodeHasChildren(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('hasChildren')->willReturn(true);
        $this->treenodeCollection->expects($this->once())->method('getElement')->with($nodeId)->willReturn($node);
        self::expectException(DeleteInteriorNodeException::class);

        $this->tree->initialize($this->treeId);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * @return void
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\Tree::deleteNode
     */
    public function testDeleteSucceedsWithOneNode(): void
    {
        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('hasChildren')->willReturn(false);
        $this->treenodeCollection->method('getElement')->with($nodeId)->willReturnOnConsecutiveCalls(null, $node);
        $this->treenodeCollection->expects($this->once())->method('delete')->with($nodeId);

        /**
         * add node as root
         */
        $this->tree->initialize($this->treeId);
        $parent = null;
        $this->tree->addNode($node, $parent);

        /**
         * verify tree has no root set any more
         */
        $this->tree->deleteNode($nodeId);
        self::assertNull($this->tree->getRoot());
    }

    /**
     * @return void
     * @throws TreeNotInitializedException
     * @throws \pvc\struct\tree\err\NoRootFoundException
     * @covers \pvc\struct\tree\Tree::hydrate
     */
    public function testHydrateThrowsExceptionWhenTreeNotInitialized(): void
    {
        $nodeId = 1;
        $parentId = null;
        $treeId = null;
        $index = 1;

        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getNodeId')->willReturn($nodeId);
        $dto->method('getParentId')->willReturn($parentId);
        $dto->method('getIndex')->willReturn($index);
        $dto->method('getTreeId')->willReturn($treeId);

        self::expectException(TreeNotInitializedException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws TreeNotInitializedException
     * @throws \pvc\struct\tree\err\NoRootFoundException
     * @covers \pvc\struct\tree\Tree::hydrate
     */
    public function testHydrateFailsWithNonEmptyArrayWhichHasNoRoot(): void
    {
        $nodeId = 1;
        $parentId = 2;
        $treeId = null;
        $index = 1;

        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getNodeId')->willReturn($nodeId);
        $dto->method('getParentId')->willReturn($parentId);
        $dto->method('getIndex')->willReturn($index);
        $dto->method('getTreeId')->willReturn($treeId);

        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        self::expectException(NoRootFoundException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws NoRootFoundException
     * @throws TreeNotInitializedException
     * @covers \pvc\struct\tree\Tree::hydrate
     * @covers \pvc\struct\tree\Tree::insertNodeRecurse
     */
    public function testHydrateFailsWhenDtoArrayHasDtoWithNonMatchingTreeId(): void
    {
        $nodeId = 1;
        $parentId = null;
        $treeId = 9;
        $index = 1;

        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getNodeId')->willReturn($nodeId);
        $dto->method('getParentId')->willReturn($parentId);
        $dto->method('getIndex')->willReturn($index);
        $dto->method('getTreeId')->willReturn($treeId);

        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $mockNode = $this->createMock(TreenodeInterface::class);
        $mockNode->expects($this->once())->method('setNodeId')->with($nodeId);
        $this->treenodeFactory->method('makeNode')->willReturn($mockNode);


        self::expectException(InvalidTreeidException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\Tree::hydrate
     * @covers \pvc\struct\tree\Tree::insertNodeRecurse
     * @covers \pvc\struct\tree\Tree::addNode
     */
    public function testHydrate(): void
    {
        $nodeId = 1;
        $parentId = null;
        $treeId = null;
        $index = 1;

        $dto = $this->createMock(TreenodeDtoInterface::class);
        $dto->method('getNodeId')->willReturn($nodeId);
        $dto->method('getParentId')->willReturn($parentId);
        $dto->method('getIndex')->willReturn($index);
        $dto->method('getTreeId')->willReturn($treeId);

        $this->treeIdTester->method('testValue')->willReturn(true);
        $this->tree->initialize($this->treeId);

        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('getParent')->willReturn(null);
        $this->treenodeFactory->expects(self::once())->method('makeNode')
            ->willReturn($node);
        $this->treenodeCollection->expects(self::once())->method('add')->with($node, $nodeId);

        $node->expects($this->once())->method('setNodeId')->with($nodeId);
        $node->expects($this->once())->method('setParent')->with($parentId);
        $node->expects($this->once())->method('setIndex')->with($index);

        $this->tree->initialize($this->treeId);
        $this->tree->hydrate([$nodeId => $dto]);
        self::assertEquals($node, $this->tree->getRoot());
    }

    /**
     * other methods tested in the integration tests
     */
}
