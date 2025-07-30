<?php

namespace pvcExamples\struct\tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\tree\err\ChildCollectionException;
use pvcExamples\struct\unordered\TreenodeFactoryUnordered;
use pvcExamples\struct\unordered\TreenodeUnordered;

class TreenodeFactoryUnorderedTest extends TestCase
{
    /**
     * @var CollectionFactory<TreenodeUnordered>|MockObject
     */
    protected CollectionFactory|MockObject $collectionFactory;

    /**
     * @var TreenodeFactoryUnordered
     */
    protected TreenodeFactoryUnordered $factory;

    public function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->factory = new TreenodeFactoryUnordered($this->collectionFactory);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactoryUnordered::class, $this->factory);
    }

    /**
     * @return void
     * @throws ChildCollectionException
     * @covers \pvcExamples\struct\unordered\TreenodeFactoryUnordered::makeNode
     */
    public function testMakeNode(): void
    {
        $mockCollection = $this->createMock(Collection::class);
        $this->collectionFactory->expects(self::once())->method(
            'makeCollection'
        )->willReturn($mockCollection);
        $mockCollection->expects($this->once())->method('isEmpty')->willReturn(
            true
        );
        self::assertInstanceOf(
            TreenodeUnordered::class,
            $this->factory->makeNode()
        );
    }


}
