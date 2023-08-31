<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\fixture;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\struct\tree\node\TreenodeAbstract;
use pvc\testingutils\testingTraits\IteratorTrait;

/**
 * Class TreenodeTestingFixture
 */
abstract class TreenodeTestingFixtureAbstract extends TestCase
{
    use IteratorTrait;

    protected int $treeId;
    protected $mockTree;

    protected int $rootNodeId;
    protected int $childNodeId;
    protected int $grandChildNodeid;


    protected $root;

    protected $child;
    protected $grandChild;

    protected CollectionAbstractInterface|MockObject $rootSiblingsCollection;

    protected $children;
    protected $grandChildren;
    protected $greatGrandChildren;

    protected $collectionFactory;

    protected $nodeTypeFactory;

    public function getTreeId(): int
    {
        return $this->treeId;
    }

    /**
     * @return mixed
     */
    public function getMockTree()
    {
        return $this->mockTree;
    }

    public function getRootNodeId(): int
    {
        return $this->rootNodeId;
    }

    public function getChildNodeId(): int
    {
        return $this->childNodeId;
    }

    public function getGrandChildNodeid(): int
    {
        return $this->grandChildNodeid;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return mixed
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * @return mixed
     */
    public function getGrandChild()
    {
        return $this->grandChild;
    }

    /**
     * getRootSiblingsCollection
     * @return mixed
     */
    public function getRootSiblingsCollection()
    {
        return $this->rootSiblingsCollection;
    }

    /**
     * @return mixed
     */
    public function getGrandChildren()
    {
        return $this->grandChildren;
    }

    /**
     * @return mixed
     */
    public function getGreatGrandChildren()
    {
        return $this->greatGrandChildren;
    }

    public function createMockTree(string $collectionClassString, string $treeTypeClassString)
    {
        $this->treeId = 0;
        $this->rootNodeId = 0;
        $this->childNodeId = 1;
        $this->grandChildNodeid = 2;

        $this->setUpTreeMock($treeTypeClassString);
        $this->createCollectionMocks($collectionClassString);
        $this->makeNodes();
        $this->makeChildCollectionMocksIterable();
    }

    public function setUpTreeMock(string $treeTypeClassString): void
    {
        $getNodeCallback = function (int $nodeId) {
            return match ($nodeId) {
                $this->rootNodeId => $this->root ?? null,
                $this->childNodeId => $this->child ?? null,
                $this->grandChildNodeid => $this->grandChild ?? null
            };
        };

        $getRootCallback = function () {
            return $this->root ?? null;
        };

        $rootTestCallback = function (TreenodeAbstractInterface $node) {
            return (is_null($node->getParentId()));
        };

        $this->mockTree = $this->createMock($treeTypeClassString);
        $this->mockTree->method('getTreeId')->willReturn($this->treeId);
        $this->mockTree->method('getRoot')->willReturnCallback($getRootCallback);
        $this->mockTree->method('getNode')->willReturnCallback($getNodeCallback);
        $this->mockTree->method('rootTest')->willReturnCallback($rootTestCallback);
    }

    public function createCollectionMocks(string $classString): void
    {
        $this->rootSiblingsCollection = $this->createMock($classString);

        $this->children = $this->createMock($classString);
        $this->grandChildren = $this->createMock($classString);
        $this->greatGrandChildren = $this->createMock($classString);

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

        /**
         * the only time the tree is called upon to make a collection is in the course of a request from the root
         * node when it tries to get its siblings.
         */
        $this->rootSiblingsCollection->method('count')->willReturn(1);
        $this->rootSiblingsCollection->method('current')->willReturnCallback([$this, 'getRoot']);
        $this->mockTree->method('makeCollection')->willReturn($this->rootSiblingsCollection);
    }

    public function makeNodes(): void
    {
        /**
         * make the nodes
         */
        $this->root = new TreenodeAbstract(
            $this->rootNodeId,
            null,
            $this->treeId,
            $this->mockTree,
            $this->children
        );

        $this->child = new TreenodeAbstract(
            $this->childNodeId,
            $this->rootNodeId,
            $this->treeId,
            $this->mockTree,
            $this->grandChildren
        );
        $this->grandChild = new TreenodeAbstract(
            $this->grandChildNodeid,
            $this->childNodeId,
            $this->treeId,
            $this->mockTree,
            $this->greatGrandChildren
        );
    }

    public function makeChildCollectionMocksIterable(): void
    {
        /**
         * make children iterable - use a pvc testing trait called mockIterator
         */
        $childrenArray = [$this->child];
        $this->mockIterator($this->root->getChildren(), $childrenArray);
        $this->root->getChildren()->method('getElements')->willReturn([$this->child]);

        $grandChildrenArray = [$this->grandChild];
        $this->mockIterator($this->child->getChildren(), $grandChildrenArray);
        $this->child->getChildren()->method('getElements')->willReturn([$this->grandChild]);

        $greatGrandChildrenArray = [];
        $this->mockIterator($this->grandChild->getChildren(), $greatGrandChildrenArray);
        $this->grandChild->getChildren()->method('getElements')->willReturn([]);
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }
}
