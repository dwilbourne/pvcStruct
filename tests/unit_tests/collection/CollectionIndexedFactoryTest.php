<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;

class CollectionIndexedFactoryTest extends TestCase
{
    protected CollectionIndexedFactory $collectionIndexedFactory;

    public function setUp() : void
    {
        $this->collectionIndexedFactory = new CollectionIndexedFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionIndexedFactory::makeCollection
     */
    public function testMakeCollectionWithEmptyArray(): void
    {
        $collection = $this->collectionIndexedFactory->makeCollection();
        self::assertInstanceOf(CollectionIndexed::class, $collection);
        self::assertTrue($collection->isEmpty());
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionIndexedFactory::makeCollection
     */
    public function testMakeCollectionWithArray(): void
    {
        $element = $this->createMock(IndexedElementInterface::class);
        $collection = $this->collectionIndexedFactory->makeCollection([$element]);
        self::assertInstanceOf(CollectionIndexed::class, $collection);
        self::assertEquals(1, $collection->count());
    }

}
