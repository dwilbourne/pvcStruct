<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\tree\TreeOrdered;

class TreenodeFactoryOrderedTest extends TestCase
{
    /**
     * @var CollectionOrderedFactory<TreenodeOrdered>|MockObject
     */
    protected CollectionOrderedFactory|MockObject $collectionFactory;

    /**
     * @var TreeOrdered&MockObject
     */
    protected TreeOrdered $tree;

    /**
     * @var int
     */
    protected int $treeId;

    /**
     * @var TreenodeFactoryOrdered
     */
    protected TreenodeFactoryOrdered $factory;

    public function setUp(): void
    {
        $this->tree = $this->createMock(TreeOrdered::class);
        $this->treeId = 1;
        $this->collectionFactory = $this->createMock(CollectionOrderedFactory::class);
        $this->factory = new TreenodeFactoryOrdered($this->collectionFactory);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactoryOrdered::class, $this->factory);
    }

    protected function initializeFactory(): void
    {
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->tree->method('isInitialized')->willReturn(true);
        $this->factory->initialize($this->tree);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\TreenodeFactoryOrdered::makeNode
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
     * @covers \pvc\struct\tree\node\TreenodeFactoryOrdered::makeNode
     */
    public function testMakeNode(): void
    {
        $this->initializeFactory();
        $this->tree->method('getTreeId')->willReturn(1);
        $mockCollection = $this->createMock(CollectionOrdered::class);
        $this->collectionFactory->expects(self::once())->method('makeCollection')->willReturn($mockCollection);
        $mockCollection->expects($this->once())->method('isEmpty')->willReturn(true);
        self::assertInstanceOf(TreenodeOrdered::class, $this->factory->makeNode());
    }


}
