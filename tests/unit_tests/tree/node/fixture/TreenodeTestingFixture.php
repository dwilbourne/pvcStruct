<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\fixture;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\tree\dto\TreenodeDtoUnordered;
use pvc\struct\tree\node\Treenode;
use pvc\testingutils\testingTraits\IteratorTrait;

/**
 * @template PayloadType of HasPayloadInterface
 * Class TreenodeTestingFixture
 * @phpstan-import-type TreenodeDtoShape from TreenodeDtoInterface
 */
class TreenodeTestingFixture extends TestCase
{
    use IteratorTrait;

    /**
     * @var non-negative-int
     */
    public int $treeId;

    /**
     * @var MockObject&TreeInterface<PayloadType>
     */
    public MockObject&TreeInterface $mockTree;

    /**
     * @var non-negative-int
     */
    public int $rootNodeId;

    /**
     * @var non-negative-int
     */
    public int $childNodeId;

    /**
     * @var non-negative-int
     */
    public int $grandChildNodeId;

    /**
     * @var TreenodeInterface<PayloadType>
     */
    public TreenodeInterface $root;

    /**
     * @var TreenodeInterface<PayloadType>
     */
    public TreenodeInterface $child;

    /**
     * @var TreenodeInterface<PayloadType>
     */
    public TreenodeInterface $grandChild;

    /**
     * @var TreenodeCollectionInterface<PayloadType>&MockObject
     */
    public TreenodeCollectionInterface&MockObject $rootSiblingsCollection;

    /**
     * @var TreenodeCollectionInterface<PayloadType>&MockObject
     */
    public TreenodeCollectionInterface&MockObject $children;

    /**
     * @var TreenodeCollectionInterface<PayloadType>&MockObject
     */
    public TreenodeCollectionInterface&MockObject $grandChildren;

    /**
     * @var TreenodeCollectionInterface<PayloadType>&MockObject
     */
    public TreenodeCollectionInterface&MockObject $greatGrandChildren;

    public function setUp(): void
    {
        $this->treeId = 0;
        $this->rootNodeId = 0;
        $this->childNodeId = 1;
        $this->grandChildNodeId = 2;

        $this->makeMockTree();
        $this->createCollectionMocks();
        $this->makeNodes();
        $this->makeChildCollectionMocksIterable();
    }

    /**
     * makeMockTree
     */
    public function makeMockTree(): void
    {
        $getNodeCallback = function (int $nodeId) {
            return match ($nodeId) {
                $this->rootNodeId => $this->root ?? null,
                $this->childNodeId => $this->child ?? null,
                $this->grandChildNodeId => $this->grandChild ?? null,
                default => null,
            };
        };

        $getRootCallback = function () {
            return $this->root ?? null;
        };

        $rootTestCallback = function (TreenodeInterface $node) {
            return (is_null($node->getParentId()));
        };

        $this->mockTree = $this->createMock(TreeInterface::class);
        $this->mockTree->method('getTreeId')->willReturn($this->treeId);
        $this->mockTree->method('getRoot')->willReturnCallback($getRootCallback);
        $this->mockTree->method('getNode')->willReturnCallback($getNodeCallback);
        $this->mockTree->method('rootTest')->willReturnCallback($rootTestCallback);
    }

    public function createCollectionMocks(): void
    {
        $this->rootSiblingsCollection = $this->createMock(TreenodeCollectionInterface::class);
        $this->children = $this->createMock(TreenodeCollectionInterface::class);
        $this->grandChildren = $this->createMock(TreenodeCollectionInterface::class);
        $this->greatGrandChildren = $this->createMock(TreenodeCollectionInterface::class);

        $childrenIsEmptyCallback = function () {
            return !isset($this->root);
        };

        $grandChildrenIsEmptyCallback = function () {
            return !isset($this->child);
        };

        $greatGrandChildrenIsEmptyCallback = function () {
            return true;
        };

        $this->children->method('isEmpty')->willReturnCallback($childrenIsEmptyCallback);
        $this->grandChildren->method('isEmpty')->willReturnCallback($grandChildrenIsEmptyCallback);
        $this->greatGrandChildren->method('isEmpty')->willReturnCallback($greatGrandChildrenIsEmptyCallback);
    }

    public function makeNodes(): void
    {
        $tester = $this->createStub(ValTesterInterface::class);
        $tester->method('testValue')->willReturn(true);

        /**
         * don't set the value of $this->root until after the node is hydrated or you generate a 'node already in the
         * tree exception' because of the way the callback is structured for the mock tree getNode method.
         */
        $root = new Treenode($this->children, $this->mockTree, $tester);
        $dto = $this->makeDTO($this->rootNodeId, null);
        $root->hydrate($dto);
        $this->root = $root;

        $child = new Treenode($this->grandChildren, $this->mockTree, $tester);
        $dto = $this->makeDTO($this->childNodeId, $this->rootNodeId);
        $child->hydrate($dto);
        $this->child = $child;

        $grandChild = new Treenode($this->greatGrandChildren, $this->mockTree, $tester);
        $dto = $this->makeDTO($this->grandChildNodeId, $this->childNodeId);
        $grandChild->hydrate($dto);
        $this->grandChild = $grandChild;
    }

    public function makeChildCollectionMocksIterable(): void
    {
        /**
         * make children iterable - use a pvc testing utility called makeMockIterableOverArray
         */
        $childrenArray = [$this->child];
        $this->makeMockIterableOverArray($this->root->getChildren(), $childrenArray);
        $this->root->getChildren()->method('getElements')->willReturn([$this->child]);

        $grandChildrenArray = [$this->grandChild];
        $this->makeMockIterableOverArray($this->child->getChildren(), $grandChildrenArray);
        $this->child->getChildren()->method('getElements')->willReturn([$this->grandChild]);

        $greatGrandChildrenArray = [];
        $this->makeMockIterableOverArray($this->grandChild->getChildren(), $greatGrandChildrenArray);
        $this->grandChild->getChildren()->method('getElements')->willReturn([]);
    }

    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @return TreenodeDtoShape&TreenodeDtoUnordered<PayloadType>
     */
    public function makeDTO(int $nodeId, int|null $parentId): TreenodeDtoUnordered
    {
        $dto = new TreenodeDtoUnordered();
        $dto->nodeId = $nodeId;
        $dto->parentId = $parentId;
        $dto->treeId = $this->treeId;
        $dto->payload = null;
        $dto->index = 0;
        return $dto;
    }
}
