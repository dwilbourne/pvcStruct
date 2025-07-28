<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;

class CollectionOrderedFactoryTest extends TestCase
{
    protected CollectionOrderedFactory $collectionIndexedFactory;

    public function setUp(): void
    {
        $this->collectionIndexedFactory = new CollectionOrderedFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrderedFactory::makeCollection
     */
    public function testMakeCollectionWithEmptyArray(): void
    {
        $collection = $this->collectionIndexedFactory->makeCollection();
        self::assertInstanceOf(CollectionOrdered::class, $collection);
        self::assertTrue($collection->isEmpty());
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrderedFactory::makeCollection
     */
    public function testMakeCollectionWithArray(): void
    {
        $element = $this->createMock(IndexedElementInterface::class);
        $collection = $this->collectionIndexedFactory->makeCollection([$element]
        );
        self::assertInstanceOf(CollectionOrdered::class, $collection);
        self::assertEquals(1, $collection->count());
    }

}
