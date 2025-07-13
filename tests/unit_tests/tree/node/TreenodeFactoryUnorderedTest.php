<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\node\TreenodeUnordered;
use pvc\struct\tree\tree\TreeUnordered;

class TreenodeFactoryUnorderedTest extends TestCase
{
    /**
     * @var CollectionFactory<TreenodeUnordered>|MockObject
     */
    protected CollectionFactory|MockObject $collectionFactory;

    /**
     * @var TreeUnordered&MockObject
     */
    protected TreeUnordered $tree;

    /**
     * @var int
     */
    protected int $treeId;

    /**
     * @var TreenodeFactoryUnordered
     */
    protected TreenodeFactoryUnordered $factory;

    public function setUp(): void
    {
        $this->tree = $this->createMock(TreeUnordered::class);
        $this->treeId = 1;
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->factory = new TreenodeFactoryUnordered($this->collectionFactory);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactoryUnordered::class, $this->factory);
    }

    protected function initializeFactory(): void
    {
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->tree->method('isInitialized')->willReturn(true);
        $this->factory->setTree($this->tree);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\TreenodeFactoryUnordered::makeNode
     */
    public function testMakeNodeFailsTreenodeFactoryIsNotInitialized(): void
    {
        self::expectException(TreeNodeFactoryNotInitializedException::class);
        $node = $this->factory->makeNode();
        unset($node);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\TreenodeFactoryUnordered::makeNode
     */
    public function testMakeNode(): void
    {

        $this->tree->method('getTreeId')->willReturn(1);
        $mockCollection = $this->createMock(Collection::class);
        $this->collectionFactory->expects(self::once())->method('makeCollection')->willReturn($mockCollection);
        $mockCollection->expects($this->once())->method('isEmpty')->willReturn(true);
        $this->initializeFactory();
        self::assertInstanceOf(TreenodeUnordered::class, $this->factory->makeNode());
    }


}
