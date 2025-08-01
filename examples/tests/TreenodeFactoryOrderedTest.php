<?php

namespace pvcExamples\struct\tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;
use pvc\struct\tree\err\ChildCollectionException;
use pvcExamples\struct\ordered\TreenodeFactoryOrdered;
use pvcExamples\struct\ordered\TreenodeOrdered;

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
     * @covers \pvcExamples\struct\ordered\TreenodeFactoryOrdered::makeNode
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
