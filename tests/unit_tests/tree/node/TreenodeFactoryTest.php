<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\node\TreenodeFactory;

class TreenodeFactoryTest extends TestCase
{

    /**
     * @var CollectionFactoryInterface<TreenodeInterface>|MockObject
     */
    protected CollectionFactoryInterface|MockObject $collectionFactory;

    /**
     * @var TreeInterface&MockObject
     */
    protected TreeInterface $tree;

    /**
     * @var int
     */
    protected int $treeId;

    /**
     * @var TreenodeFactoryInterface
     */
    protected TreenodeFactoryInterface $factory;

    public function setUp(): void
    {
        $this->tree = $this->createMock(TreeInterface::class);
        $this->treeId = 1;
        $this->collectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->factory = $this->getMockBuilder(TreenodeFactory::class)
            ->setConstructorArgs([$this->collectionFactory])
            ->getMockForAbstractClass();
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactory::class, $this->factory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeFactory::isInitialized
     * @covers \pvc\struct\tree\node\TreenodeFactory::initialize
     */
    public function testIsInitialized(): void
    {
        self::assertFalse($this->factory->isInitialized());
        $this->initializeFactory();
        self::assertTrue($this->factory->isInitialized());
    }

    protected function initializeFactory(): void
    {
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->tree->method('isInitialized')->willReturn(true);
        $this->factory->initialize($this->tree);
    }

    /**
     * @return void
     * @throws TreenodeFactoryNotInitializedException
     * @covers \pvc\struct\tree\node\TreenodeFactory::getTreenodeCollectionFactory
     */
    public function testGetCollectionFactoryThrowsExceptionIfTreenodeFactoryNotInitialized(): void
    {
        self::expectException(TreenodeFactoryNotInitializedException::class);
        $collectionFactory = $this->factory->getTreenodeCollectionFactory();
    }

    /**
     * testGetCollectionFactory
     * @covers \pvc\struct\tree\node\TreenodeFactory::getTreenodeCollectionFactory
     */
    public function testGetCollectionFactory(): void
    {
        $this->initializeFactory();
        self::assertEquals($this->collectionFactory, $this->factory->getTreenodeCollectionFactory());
    }
}
