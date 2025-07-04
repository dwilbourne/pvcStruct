<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\fixture;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\collection\Collection;
use pvc\struct\tree\dto\TreenodeDtoOrdered;
use pvc\struct\tree\dto\TreenodeDtoUnordered;
use pvc\struct\tree\node\Treenode;
use pvc\testingutils\testingTraits\IteratorTrait;

/**
 * Class TreenodeTestingFixture
 * @phpstan-import-type TreenodeDtoShape from TreenodeDtoUnordered
 */
class TreenodeTestingFixture extends TestCase
{
    use IteratorTrait;

    /**
     * @var non-negative-int
     */
    public int $treeId;

    /**
     * @var MockObject&TreeInterface
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
     * @var TreenodeInterface
     */
    public TreenodeInterface $root;

    /**
     * @var TreenodeInterface
     */
    public TreenodeInterface $child;

    /**
     * @var TreenodeInterface
     */
    public TreenodeInterface $grandChild;

    /**
     * @var Collection|MockObject
     */
    public CollectionInterface|MockObject $rootSiblingsCollection;

    /**
     * @var CollectionInterface|MockObject
     */
    public CollectionInterface|MockObject $children;

    /**
     * @var CollectionInterface|MockObject
     */
    public CollectionInterface|MockObject $grandChildren;

    /**
     * @var CollectionInterface|MockObject
     */
    public CollectionInterface|MockObject $greatGrandChildren;

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
        $this->rootSiblingsCollection = $this->createMock(CollectionInterface::class);
        $this->children = $this->createMock(CollectionInterface::class);
        $this->grandChildren = $this->createMock(CollectionInterface::class);
        $this->greatGrandChildren = $this->createMock(CollectionInterface::class);

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
        /**
         * don't set the value of $this->root until after the node is hydrated or you generate a 'node already in the
         * tree exception' because of the way the callback is structured for the mock tree getNode method.
         */
        $root = new Treenode($this->children, $this->mockTree);
        $dto = $this->makeDTOUnordered($this->rootNodeId, null);
        $root->hydrate($dto);
        $this->root = $root;

        $child = new Treenode($this->grandChildren, $this->mockTree);
        $dto = $this->makeDTOUnordered($this->childNodeId, $this->rootNodeId);
        $child->hydrate($dto);
        $this->child = $child;

        $grandChild = new Treenode($this->greatGrandChildren, $this->mockTree);
        $dto = $this->makeDTOUnordered($this->grandChildNodeId, $this->childNodeId);
        $grandChild->hydrate($dto);
        $this->grandChild = $grandChild;
    }

    public function makeChildCollectionMocksIterable(): void
    {
        /**
         * make children iterable - use a pvc testing utility called makeMockIterableOverArray
         */
        $childrenArray = [$this->child];
        $this->makeMockIterableOverArray($this->children, $childrenArray);
        $this->children->method('getElements')->willReturn([$this->child]);

        $grandChildrenArray = [$this->grandChild];
        $this->makeMockIterableOverArray($this->grandChildren, $grandChildrenArray);
        $this->grandChildren->method('getElements')->willReturn([$this->grandChild]);

        $greatGrandChildrenArray = [];
        $this->makeMockIterableOverArray($this->greatGrandChildren, $greatGrandChildrenArray);
        $this->greatGrandChildren->method('getElements')->willReturn([]);
    }

    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @return TreenodeDtoShape&TreenodeDtoUnordered
     */
    public function makeDTOUnordered(int $nodeId, int|null $parentId): TreenodeDtoUnordered
    {
        $payload = null;
        $dto = new TreenodeDtoUnordered($nodeId, $parentId, $this->treeId, $payload);
        return $dto;
    }

    public function makeDTOOrdered(int $nodeId, int|null $parentId, int $index): TreenodeDtoOrdered
    {
        $payload = null;
        $dto = new TreenodeDtoOrdered($nodeId, $parentId, $this->treeId, $payload, $index);
        return $dto;
    }

    public function makeDtoWithNonMatchingTreeId(int $nodeId, int|null $parentId): TreenodeDtoUnordered
    {
        $badTreeId = 100;
        $payload = null;
        $dto = new TreenodeDtoUnordered($nodeId, $parentId, $badTreeId, $payload);
        return $dto;
    }
}
