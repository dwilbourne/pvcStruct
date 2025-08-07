<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\tree;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\struct\tree\tree\TreenodeCollectionInterface;
use pvc\struct\collection\err\NonExistentKeyException;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreenodeCollection;

/**
 * Class TreeTest
 * @template TreenodeType of TreenodeInterface
 */
class TreeTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreeInterface
     */
    protected TreeInterface $tree;

    /**
     * @var TreenodeFactoryInterface<TreenodeType>
     */
    protected TreenodeFactoryInterface&MockObject $treenodeFactory;

    /**
     * @var TreenodeCollectionInterface<TreenodeType>&MockObject
     */
    protected $collection;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->collection = $this->createMock(TreenodeCollectionInterface::class);
        $this->treenodeFactory = $this->createMock(
            TreenodeFactoryInterface::class
        );
        $this->tree = new Tree($this->treenodeFactory, $this->collection);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\tree\Tree::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(Tree::class, $this->tree);
    }

    /**
     * testSetInvalidTreeidThrowsException
     *
     * @throws Exception
     * @covers \pvc\struct\tree\tree\Tree::setTreeId
     * @covers \pvc\struct\tree\tree\Tree::validateTreeId
     */
    public function testSetInvalidTreeidThrowsException(): void
    {
        self::expectException(InvalidTreeidException::class);
        /**
         * treeids must be integers >= 0
         */
        $this->tree->initialize(-2);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::setTreeId
     * @covers \pvc\struct\tree\tree\Tree::isEmpty
     */
    public function testInitializeIsEmpty(): void
    {
        $this->collection->expects($this->once())->method('initialize');
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->tree->initialize($this->treeId);
        self::assertTrue($this->tree->isEmpty());
    }

    /**
     * testRootTest
     *
     * @covers \pvc\struct\tree\tree\Tree::rootTest
     */
    public function testRootTestOnDTO(): void
    {
        /**
         * @var TreenodeDto $dto
         * cannot mock a dto - readonly classes cannot be doubled
         */
        $nodeId = 1;
        $treeId = null;
        $parentId = null;
        $index = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);
        self::assertTrue($this->tree->rootTest($dto));

        $parentId = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);
        self::assertFalse($this->tree->rootTest($dto));
    }

    /**
     * testRootTestOnNode
     *
     * @covers \pvc\struct\tree\tree\Tree::rootTest
     */
    public function testRootTestOnNode(): void
    {
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getParent')->willReturnOnConsecutiveCalls(null, $node);
        self::assertTrue($this->tree->rootTest($node));
        self::assertFalse($this->tree->rootTest($node));
    }

    /**
     * testWhenTreeHasNoNodes
     *
     * @covers \pvc\struct\tree\tree\Tree::isEmpty
     * @covers \pvc\struct\tree\tree\Tree::getNodeCollection
     * @covers \pvc\struct\tree\tree\Tree::getNode
     * @covers \pvc\struct\tree\tree\Tree::getRoot
     */
    public function testWhenTreeHasNoNodes(): void
    {
        $this->collection->method('isEmpty')->willReturn(true);
        self::assertTrue($this->tree->isEmpty());
        self::assertNull($this->tree->getRoot());
        $nonExistentNodeId = 5;
        $this->collection->method('getElement')->with($nonExistentNodeId)->willReturn($this->throwException(new NonExistentKeyException($nonExistentNodeId)));
        self::assertNull($this->tree->getNode($nonExistentNodeId));
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\tree\Tree::addNode
     * @covers \pvc\struct\tree\tree\Tree::setRoot
     * @covers \pvc\struct\tree\tree\Tree::getRoot
     * @covers \pvc\struct\tree\tree\Tree::getNodeCollection
     */
    public function testAdd(): void
    {
        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->expects($this->once())->method('setParent')->with(null);
        $node->method('getParent')->willReturn(null);
        $this->collection->expects($this->exactly(2))->method('getElement')->with($nodeId)->willReturnOnConsecutiveCalls(null, $node);
        $this->collection->expects($this->once())->method('add')->with($nodeId, $node);
        $this->tree->addNode($node, null);
        self::assertSame($node, $this->tree->getRoot());

        self::assertSame($this->collection, $this->tree->getNodeCollection());

        /**
         * adding it a second time produces an exception
         */
        self::expectException(AlreadySetNodeidException::class);
        $this->tree->addNode($node, null);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\tree\Tree::setRoot
     */
    public function testAddingRootASecondTimeFails(): void
    {
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


        $this->collection->expects($this->exactly(2))->method('getElement')->willReturn(null);

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
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     */
    public function testDeleteFailsWhenNodeIsNotInTree(): void
    {
        $nodeId = 1;
        $this->collection->expects($this->once())->method('getElement')->with($nodeId)->willReturn(null);
        self::expectException(NodeNotInTreeException::class);

        $this->tree->initialize($this->treeId);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * @return void
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     */
    public function testDeleteFailsWhenDeleteBranchIsFalseAndNodeHasChildren(): void
    {
        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('hasChildren')->willReturn(true);
        $this->collection->expects($this->once())->method('getElement')->with($nodeId)->willReturn($node);
        self::expectException(DeleteInteriorNodeException::class);

        $this->tree->initialize($this->treeId);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * @return void
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     */
    public function testDeleteSucceedsWithOneNode(): void
    {
        $nodeId = 1;
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('hasChildren')->willReturn(false);
        $this->collection->method('getElement')->with($nodeId)->willReturnOnConsecutiveCalls(null, $node);
        $this->collection->expects($this->once())->method('delete')->with($nodeId);

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
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testHydrateThrowsExceptionWhenTreeNotInitialized(): void
    {
        /**
         * have to make a real dto.  Readonly classes cannot be doubled
         */
        $nodeId = 1;
        $parentId = null;
        $treeId = null;
        $index = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);

        self::expectException(TreeNotInitializedException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws TreeNotInitializedException
     * @throws \pvc\struct\tree\err\NoRootFoundException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testHydrateFailsWithNonEmptyArrayWhichHasNoRoot(): void
    {
        /**
         * have to make a real dto.  Readonly classes cannot be doubled
         */
        $nodeId = 1;
        $parentId = 2;
        $treeId = null;
        $index = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);

        $this->tree->initialize($this->treeId);
        self::expectException(NoRootFoundException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws NoRootFoundException
     * @throws TreeNotInitializedException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     */
    public function testHydrateFailsWhenDtoArrayHasDtoWithNonMatchingTreeId(): void
    {
        /**
         * have to make a real dto.  Readonly classes cannot be doubled
         */
        $nodeId = 1;
        $parentId = null;
        $treeId = 9;
        $index = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);

        $mockNode = $this->createMock(TreenodeInterface::class);
        $mockNode->expects($this->once())->method('setNodeId')->with($nodeId);
        $this->treenodeFactory->method('makeNode')->willReturn($mockNode);

        $this->tree->initialize($this->treeId);
        self::expectException(InvalidTreeidException::class);
        $this->tree->hydrate([$nodeId => $dto]);
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     * @covers \pvc\struct\tree\tree\Tree::insertNodeRecurse
     * @covers \pvc\struct\tree\tree\Tree::addNode
     */
    public function testHydrate(): void
    {
        /**
         * have to make a real dto.  Readonly classes cannot be doubled
         */
        $nodeId = 1;
        $parentId = null;
        $treeId = null;
        $index = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);

        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('getParent')->willReturn(null);
        $this->treenodeFactory->expects(self::once())->method('makeNode')
            ->willReturn($node);
        $this->collection->expects(self::once())->method('add')->with($nodeId, $node);

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
