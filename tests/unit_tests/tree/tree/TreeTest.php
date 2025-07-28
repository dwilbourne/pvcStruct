<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\tree;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;
use pvc\struct\tree\tree\Tree;

/**
 * Class AbstractTreeTest
 * @template TreenodeType of TreenodeInterface
 * @template TreeType of TreeInterface
 * @template CollectionType of CollectionInterface
 * @phpstan-import-type TreenodeDtoShape from TreenodeDto
 */
class TreeTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreeType
     */
    protected Tree $tree;

    /**
     * @var CollectionFactoryInterface<TreenodeType>&MockObject
     */
    protected CollectionFactoryInterface&MockObject $collectionFactory;

    protected TreenodeFactoryInterface&MockObject $treenodeFactory;

    /**
     * @var CollectionInterface<TreenodeType>&MockObject
     */
    protected $collection;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->collection = $this->createMock(CollectionInterface::class);
        $this->collectionFactory = $this->createMock(
            CollectionFactoryInterface::class
        );
        $this->collectionFactory->method('makeCollection')->willReturn(
            $this->collection
        );
        $this->treenodeFactory = $this->createMock(
            TreenodeFactoryInterface::class
        );
        $this->tree = new Tree($this->treenodeFactory);
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
     * @return void
     * @covers \pvc\struct\tree\tree\Tree::isInitialized
     * @covers \pvc\struct\tree\tree\Tree::initialize
     * @covers \pvc\struct\tree\tree\Tree::setTreeId
     * @covers \pvc\struct\tree\tree\Tree::getTreeId
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testIsInitialized(): void
    {
        self::assertFalse($this->tree->isInitialized());
        $this->tree->initialize($this->treeId);
        self::assertTrue($this->tree->isInitialized());
        self::assertEquals($this->treeId, $this->tree->getTreeId());
        /**
         * show that tree is empty when second parameter to the tre::initialize method is omitted
         */
        self::assertTrue($this->tree->isEmpty());
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
     * testMakeCollection
     *
     * @covers \pvc\struct\tree\tree\Tree::makeCollection
     */
    public function testMakeCollection(): void
    {
        $this->tree->initialize($this->treeId);
        $this->treenodeFactory->expects(self::once())->method('makeCollection')
            ->willReturn($this->collection);
        self::assertEquals($this->collection, $this->tree->makeCollection());
    }

    /**
     * testRootTest
     *
     * @covers \pvc\struct\tree\tree\Tree::rootTest
     */
    public function testRootTestOnDTO(): void
    {
        /**
         * @var TreenodeDtoShape $dto
         */
        $nodeId = 1;
        $treeId = null;

        $parentId = null;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
        self::assertTrue($this->tree->rootTest($dto));

        $parentId = 1;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
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
     * @covers \pvc\struct\tree\tree\Tree::nodeCount
     * @covers \pvc\struct\tree\tree\Tree::getNodes
     * @covers \pvc\struct\tree\tree\Tree::getNode
     * @covers \pvc\struct\tree\tree\Tree::getRoot
     */
    public function testWhenTreeHasNoNodes(): void
    {
        self::assertTrue($this->tree->isEmpty());
        self::assertEquals(0, $this->tree->nodeCount());
        self::assertEquals(0, count($this->tree->getNodes()));
        self::assertNull($this->tree->getNode(0));
        self::assertNull($this->tree->getRoot());
    }

    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @covers \pvc\struct\tree\tree\Tree::addNode
     */
    public function testAddNodeCreatesNodeFromDto(): void
    {
        /**
         * have to make a real dto.  Readonly classes cannot be doubled
         */
        $nodeId = 1;
        $parentId = null;
        $treeId = null;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);

        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $this->treenodeFactory->expects(self::once())->method('makeNode')
            ->willReturn($node);
        $node->expects($this->once())->method('hydrate')->with($dto);
        $this->tree->initialize($this->treeId);
        $this->tree->addNode($dto);
        self::assertEquals($node, $this->tree->getNode($nodeId));
    }

    /**
     * testWhenTreeHasOneNode
     *
     * @covers \pvc\struct\tree\tree\Tree::addNode
     * @covers \pvc\struct\tree\tree\Tree::isEmpty
     * @covers \pvc\struct\tree\tree\Tree::nodeCount
     * @covers \pvc\struct\tree\tree\Tree::getNodes
     * @covers \pvc\struct\tree\tree\Tree::getNode
     * @covers \pvc\struct\tree\tree\Tree::setRoot
     * @covers \pvc\struct\tree\tree\Tree::getRoot
     * @covers \pvc\struct\tree\tree\Tree::initialize
     */
    public function testWhenTreeHasOneNode(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('getNodeId')->willReturn($rootId);
        $this->tree->initialize($this->treeId);
        $this->tree->addNode($root);

        self::assertFalse($this->tree->isEmpty());
        self::assertEquals(1, $this->tree->nodeCount());
        self::assertEqualsCanonicalizing([$root], $this->tree->getNodes());
        self::assertEquals($root, $this->tree->getNode($rootId));
        self::assertEquals($root, $this->tree->getRoot());

        $this->tree->initialize($this->treeId);
        self::assertTrue($this->tree->isEmpty());
        self::assertEquals(0, $this->tree->nodeCount());
    }

    /**
     * @param  int  $rootId
     *
     * @return TreenodeType&MockObject
     */
    public function createMockRoot(int $rootId): TreenodeInterface&MockObject
    {
        $root = $this->createMock(TreenodeInterface::class);
        $root->method('getNodeId')->willReturn($rootId);
        $root->method('getParent')->willReturn(null);
        // $root->expects($this->once())->method('hydrate');
        return $root;
    }

    /**
     * testSetNodeIdFailsWhenNodeWithSameNodeIdAlreadyExistsInTree
     *
     * @covers \pvc\struct\tree\tree\Tree::addNode
     * @throws InvalidNodeIdException
     */
    public function testAddNodeFailsWhenNodeIdAlreadyExistsInTree(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('getNodeId')->willReturn($rootId);
        $this->tree->initialize($this->treeId);
        $this->tree->addNode($root);
        self::expectException(AlreadySetNodeidException::class);
        $this->tree->addNode($root);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\tree\Tree::setRoot
     */
    public function testAddingRootASecondTimeThrowsException(): void
    {
        $this->tree->initialize($this->treeId);

        $firstRootId = 0;
        $secondRootId = 1;

        $firstRoot = $this->createMockRoot($firstRootId);
        $secondRoot = $this->createMockRoot($secondRootId);

        /**
         * first time is fine
         */
        $this->tree->addNode($firstRoot);

        /**
         * second time throws an exception
         */
        self::expectException(AlreadySetRootException::class);
        $this->tree->addNode($secondRoot);
    }

    /**
     * testWhenTreeHasTwoNodes
     *
     * @covers \pvc\struct\tree\tree\Tree::addNode
     * @covers \pvc\struct\tree\tree\Tree::isEmpty
     * @covers \pvc\struct\tree\tree\Tree::getNodes
     * @covers \pvc\struct\tree\tree\Tree::getNode
     */
    public function testWhenTreeHasTwoNodes(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);

        $nodeId = 1;
        $node = $this->createMockNodeWithRootAsParent($nodeId, $root);

        $this->tree->initialize($this->treeId);

        $this->tree->addNode($root);
        $this->tree->addNode($node);

        self::assertFalse($this->tree->isEmpty());
        self::assertEquals(2, $this->tree->nodeCount());
        self::assertEqualsCanonicalizing([$root, $node],
            $this->tree->getNodes());
        self::assertEquals($root, $this->tree->getNode($rootId));
        self::assertEquals($node, $this->tree->getNode($nodeId));
        self::assertEquals($root, $this->tree->getRoot());
    }

    /**
     * @param  int  $nodeId
     * @param  int  $rootId
     *
     * @return TreenodeType&MockObject
     */
    public function createMockNodeWithRootAsParent(
        int $nodeId,
        TreenodeInterface $root
    ): TreenodeInterface&MockObject {
        $node = $this->createMock(TreenodeInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('getParent')->willReturn($root);
        return $node;
    }

    /**
     * testHydrateThrowsExceptionWhenNoRootValueObjectFoundInArray
     *
     * @throws NoRootFoundException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testHydrateThrowsExceptionWhenNoRootDtoFoundInArray(): void
    {
        /**
         * @var TreenodeDtoShape $dto
         */
        $nodeId = 2;
        $parentId = 1;
        $treeId = null;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);

        $this->tree->initialize($this->treeId);
        self::expectException(NoRootFoundException::class);
        $this->tree->hydrate([$dto]);
    }

    /**
     * @return void
     * @throws NoRootFoundException
     * @throws TreeNotInitializedException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testHydrateThrowsExceptionWhenTreeNotInitialized(): void
    {
        self::expectException(TreeNotInitializedException::class);
        $this->tree->hydrate([]);
    }

    /**
     * @return void
     * @throws NoRootFoundException
     * @throws TreeNotInitializedException
     * @covers \pvc\struct\tree\tree\Tree::hydrate
     */
    public function testHydrateDoesNothingWithEmptyParameter(): void
    {
        $this->tree->initialize($this->treeId);
        $this->tree->hydrate([]);
        self::assertTrue($this->tree->isEmpty());
    }

    /**
     * testDeleteNodeThrowsExceptionWhenNodeIsNotInTree
     *
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     */
    public function testDeleteNodeThrowsExceptionWhenNodeIsNotInTree(): void
    {
        $this->tree->initialize($this->treeId);
        /**
         * tree is empty
         */
        $nodeId = 1;
        $this->expectException(NodeNotInTreeException::class);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse
     *
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     */
    public function testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse(
    ): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('hasChildren')->willReturn(true);

        $this->tree->initialize($this->treeId);

        $this->tree->addNode($root);
        $this->expectException(DeleteInteriorNodeException::class);
        $deleteBranch = false;
        $this->tree->deleteNode($rootId, $deleteBranch);
    }

    /**
     * testDeleteNodeUnsetsRootIfThereAreNoNodesLeftinTree
     *
     * @covers \pvc\struct\tree\tree\Tree::deleteNode
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function testDeleteNodeUnsetsRootIfThereAreNoNodesLeftinTree(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('hasChildren')->willReturn(false);

        $collection = $this->createMock(CollectionInterface::class);
        $collection->method('getElements')->willReturn([]);
        $root->method('getChildren')->willReturn($collection);

        $this->tree->initialize($this->treeId);
        $this->tree->addNode($root);

        $deleteBranch = false;
        $this->tree->deleteNode($rootId, $deleteBranch);

        self::assertTrue($this->tree->isEmpty());
        self::assertNull($this->tree->getRoot());
    }

    /**
     * deleteNodeRecurse is tested in the integration tests
     */
}
