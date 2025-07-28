<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;

class TreenodeFactoryOrderedTest extends TestCase
{
    /**
     * @var CollectionOrderedFactory<TreenodeOrdered, CollectionOrdered>&MockObject
     */
    protected CollectionOrderedFactory&MockObject $collectionFactory;

    /**
     * @var TreenodeFactoryOrdered
     */
    protected TreenodeFactoryOrdered $factory;

    public function setUp(): void
    {
        $this->collectionFactory = $this->createMock(
            CollectionOrderedFactory::class
        );
        $this->factory = new TreenodeFactoryOrdered($this->collectionFactory);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactoryOrdered::class, $this->factory);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvc\struct\tree\node\TreenodeFactoryOrdered::makeNode
     */
    public function testMakeNode(): void
    {
        $mockCollection = $this->createMock(CollectionOrdered::class);
        $this->collectionFactory->expects(self::once())->method(
            'makeCollection'
        )->willReturn($mockCollection);
        $mockCollection->expects($this->once())->method('isEmpty')->willReturn(
            true
        );
        self::assertInstanceOf(
            TreenodeOrdered::class,
            $this->factory->makeNode()
        );
    }


}
