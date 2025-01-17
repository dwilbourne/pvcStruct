<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\tree;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\dto\TreenodeDTOUnordered;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\err\TreeNotEmptyHydrationException;
use pvc\struct\tree\tree\TreeAbstract;

/**
 * Class AbstractTreeTest
 */
class TreeAbstractTest extends TestCase
{
    /**
     * @var int
     */
    protected int $treeId;

    /**
     * @var TreeAbstractInterface|MockObject
     */
    protected TreeAbstract $tree;

    /**
     * setUp
     */
    public function setUp() : void
    {
        $this->treeId = 0;
        /** @var TreenodeFactoryInterface $factory */
        $factory = $this->createMock(TreenodeFactoryInterface::class);
        $this->tree = $this->getMockBuilder(TreeAbstract::class)
            ->setConstructorArgs([$this->treeId, $factory])
                           ->getMockForAbstractClass();
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\tree\TreeAbstract::__construct
     */
    public function testConstruct() : void
    {
        self::assertInstanceOf(TreeAbstract::class, $this->tree);
    }

    /**
     * testGetSetTreeid
     * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
     * @covers \pvc\struct\tree\tree\TreeAbstract::getTreeId
     * @covers \pvc\struct\tree\tree\TreeAbstract::validateTreeId
     */
    public function testGetSetTreeid(): void
    {
        self::assertEquals($this->treeId, $this->tree->getTreeId());
    }

    /**
     * testSetInvalidTreeidThrowsException
     * @throws Exception
     * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
     * @covers \pvc\struct\tree\tree\TreeAbstract::validateTreeId
     */
    public function testSetInvalidTreeidThrowsException() : void
    {
        self::expectException(InvalidTreeidException::class);
        /**
         * treeids must be integers >= 0
         */
        $this->tree->setTreeId(-2);
    }

    /**
     * testSetTreeIdFailsIfTreeIsNotEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::setTreeId
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function testSetTreeIdFailsIfTreeIsNotEmpty(): void
    {
        $tree = $this->getMockBuilder(TreeAbstract::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['isEmpty'])
                     ->getMockForAbstractClass();
        $tree->method('isEmpty')->willReturn(false);
        self::expectException(SetTreeIdException::class);
        $tree->setTreeId(2);
    }

    /**
     * testSetGetTreenodeFactoryInterface
     * @covers \pvc\struct\tree\tree\TreeAbstract::setTreenodeFactory
     * @covers \pvc\struct\tree\tree\TreeAbstract::getTreenodeFactory
     */
    public function testSetGetTreenodeFactoryInterface(): void
    {
        $factory = $this->createMock(TreenodeFactoryInterface::class);
        $this->tree->setTreenodeFactory($factory);
        self::assertEquals($factory, $this->tree->getTreenodeFactory());
    }

    /**
     * testRootTest
     * @covers \pvc\struct\tree\tree\TreeAbstract::rootTest
     */
    public function testRootTestOnDTO(): void
    {
        $dtoArray = ['nodeId' => 1, 'parentId' => null, 'treeId' => 1, 'payload' => null];
        $dto = new TreenodeDTOUnordered();
        $dto->hydrateFromArray($dtoArray);
        self::assertTrue($this->tree->rootTest($dto));

        $dtoArray['parentId'] = 2;
        $dto = new TreenodeDTOUnordered();
        $dto->hydrateFromArray($dtoArray);
        self::assertFalse($this->tree->rootTest($dto));
    }

    /**
     * testRootTestOnNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::rootTest
     */
    public function testRootTestOnNode(): void
    {
        $node = $this->createMock(TreenodeAbstractInterface::class);
        $node->method('getParentId')->willReturnOnConsecutiveCalls(null, 2);
        self::assertTrue($this->tree->rootTest($node));
        self::assertFalse($this->tree->rootTest($node));
    }

    public function createMockRoot(int $rootId): TreenodeAbstractInterface|MockObject
    {
        $root = $this->createMock(TreenodeAbstractInterface::class);
        $root->method('getNodeId')->willReturn($rootId);
        $root->method('getParentId')->willReturn(null);
        return $root;
    }

    public function createMockNodeWithRootAsParent(int $nodeId, int $rootId): TreenodeAbstractInterface|MockObject
    {
        $node = $this->createMock(TreenodeAbstractInterface::class);
        $node->method('getNodeId')->willReturn($nodeId);
        $node->method('getParentId')->willReturn($rootId);
        return $node;
    }

    /**
     * testWhenTreeHasNoNodes
     * @covers \pvc\struct\tree\tree\TreeAbstract::isEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::nodeCount
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNodes
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNode
     */
    public function testWhenTreeHasNoNodes(): void
    {
        self::assertTrue($this->tree->isEmpty());
        self::assertEquals(0, $this->tree->nodeCount());
        self::assertIsArray($this->tree->getNodes());
        self::assertNull($this->tree->getNode(0));
        self::assertNull($this->tree->getRoot());
    }

    /**
     * testWhenTreeHasOneNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::addNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::isEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::nodeCount
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNodes
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::setRoot
     * @covers \pvc\struct\tree\tree\TreeAbstract::getRoot
     * @covers \pvc\struct\tree\tree\TreeAbstract::initialize
     */
    public function testWhenTreeHasOneNode(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);

        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);

        $nodeFactory->method('makeNode')
                    ->willReturn($root);
        $this->tree->setTreenodeFactory($nodeFactory);

        $this->tree->addNode($valueObject);

        self::assertFalse($this->tree->isEmpty());
        self::assertEquals(1, $this->tree->nodeCount());
        self::assertEqualsCanonicalizing([$root], $this->tree->getNodes());
        self::assertEquals($root, $this->tree->getNode($rootId));
        self::assertEquals($root, $this->tree->getRoot());

        $this->tree->initialize();
        self::assertTrue($this->tree->isEmpty());
        self::assertEquals(0, $this->tree->nodeCount());
    }

    /**
     * testSettingRootASecondTimeFails
     * @covers \pvc\struct\tree\tree\TreeAbstract::setRoot
     */
    public function testSettingRootASecondTimeFails(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);

        $secondRootId = 1;
        $secondRoot = $this->createMockRoot($secondRootId);

        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);

        $nodeFactory->method('makeNode')
                    ->willReturnOnConsecutiveCalls($root, $secondRoot);
        $this->tree->setTreenodeFactory($nodeFactory);

        $this->tree->addNode($valueObject);
        self::expectException(AlreadySetRootException::class);
        $this->tree->addNode($valueObject);
    }


    /**
     * testWhenTreeHasTwoNodes
     * @covers \pvc\struct\tree\tree\TreeAbstract::addNode
     * @covers \pvc\struct\tree\tree\TreeAbstract::isEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNodes
     * @covers \pvc\struct\tree\tree\TreeAbstract::getNode
     */
    public function testWhenTreeHasTwoNodes(): void
    {
        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $rootId = 0;
        $nodeId = 1;

        $root = $this->createMockRoot($rootId);
        $node = $this->createMockNodeWithRootAsParent($nodeId, $rootId);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);
        $nodeFactory->method('makeNode')
                    ->willReturnOnConsecutiveCalls($root, $node);
        $this->tree->setTreenodeFactory($nodeFactory);

        $this->tree->addNode($valueObject);
        $this->tree->addNode($valueObject);

        self::assertFalse($this->tree->isEmpty());
        self::assertEquals(2, $this->tree->nodeCount());
        self::assertEqualsCanonicalizing([$root, $node], $this->tree->getNodes());
        self::assertEquals($root, $this->tree->getNode($rootId));
        self::assertEquals($node, $this->tree->getNode($nodeId));
        self::assertEquals($root, $this->tree->getRoot());
    }

    /**
     * testSetNodesThrowsExceptionIfCalledWhenTreeIsNotEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     */
    public function testHydrateWithEmptyNodeValueObjectArray(): void
    {
        $nodeValueObjectArray = [];
        $this->tree->hydrate($nodeValueObjectArray);
        self::assertTrue($this->tree->isEmpty());
    }

    /**
     * testHydrateFailsIfTreeIsNotEmpty
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     * @throws NoRootFoundException
     * @throws TreeNotEmptyHydrationException
     */
    public function testHydrateFailsIfTreeIsNotEmpty(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);

        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);

        $nodeFactory->method('makeNode')
                    ->willReturn($root);
        $this->tree->setTreenodeFactory($nodeFactory);

        $this->tree->addNode($valueObject);

        self::expectException(TreeNotEmptyHydrationException::class);

        $this->tree->hydrate([$valueObject]);
    }

    /**
     * testHydrateThrowsExceptionWhenNoRootValueObjectFoundInArray
     * @throws NoRootFoundException
     * @covers \pvc\struct\tree\tree\TreeAbstract::hydrate
     */
    public function testHydrateThrowsExceptionWhenNoRootDTOFoundInArray(): void
    {
        $dtoArray = ['nodeId' => 1, 'parentId' => 2, 'treeId' => 1, 'payload' => null];
        $dto = new TreenodeDTOUnordered();
        $dto->hydrateFromArray($dtoArray);

        self::expectException(NoRootFoundException::class);
        $this->tree->hydrate([$dto]);
    }

    /**
     * testDeleteNodeThrowsExceptionWhenNodeIsNotInTree
     * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNode
     */
    public function testDeleteNodeThrowsExceptionWhenNodeIsNotInTree(): void
    {
        /**
         * tree is empty
         */
        $nodeId = 1;
        $this->expectException(NodeNotInTreeException::class);
        $this->tree->deleteNode($nodeId);
    }

    /**
     * testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse
     * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNode
     */
    public function testDeleteNodeThrowsExceptionTryingToDeleteInteriorNodeWithDeleteBranchFalse() : void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('hasChildren')->willReturn(true);

        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);

        $nodeFactory->method('makeNode')
                    ->willReturn($root);
        $this->tree->setTreenodeFactory($nodeFactory);

        $this->tree->addNode($valueObject);

        $this->expectException(DeleteInteriorNodeException::class);

        $deleteBranch = false;
        $this->tree->deleteNode($rootId, $deleteBranch);
    }

    /**
     * testDeleteNodeUnsetsRootIfThereAreNoNodesLeftinTree
     * @covers \pvc\struct\tree\tree\TreeAbstract::deleteNode
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function testDeleteNodeUnsetsRootIfThereAreNoNodesLeftinTree(): void
    {
        $rootId = 0;
        $root = $this->createMockRoot($rootId);
        $root->method('hasChildren')->willReturn(false);

        $collection = $this->createMock(CollectionAbstractInterface::class);
        $collection->method('getElements')->willReturn([]);
        $root->method('getChildren')->willReturn($collection);

        $valueObject = $this->createMock(TreenodeDTOInterface::class);

        $nodeFactory = $this->createMock(TreenodeFactoryInterface::class);

        $nodeFactory->method('makeNode')
                    ->willReturn($root);
        $this->tree->setTreenodeFactory($nodeFactory);
        $this->tree->addNode($valueObject);

        $deleteBranch = false;
        $this->tree->deleteNode($rootId, $deleteBranch);

        self::assertTrue($this->tree->isEmpty());
        self::assertNull($this->tree->getRoot());
    }

    /**
     * deleteNodeRecurse is tested in the integration tests
     */
}
