<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\CollectionOrderedByIndex;
use pvc\struct\collection\CollectionOrderedByIndexFactory;

class CollectionOrderedFactoryTest extends TestCase
{
    protected CollectionOrderedByIndexFactory $collectionIndexedFactory;

    public function setUp(): void
    {
        $this->collectionIndexedFactory = new CollectionOrderedByIndexFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrderedByIndexFactory::makeCollection
     */
    public function testMakeCollectionWithEmptyArray(): void
    {
        $collection = $this->collectionIndexedFactory->makeCollection();
        self::assertInstanceOf(CollectionOrderedByIndex::class, $collection);
        self::assertTrue($collection->isEmpty());
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrderedByIndexFactory::makeCollection
     */
    public function testMakeCollectionWithArray(): void
    {
        $element = $this->createMock(IndexedElementInterface::class);
        $collection = $this->collectionIndexedFactory->makeCollection([$element]
        );
        self::assertInstanceOf(CollectionOrderedByIndex::class, $collection);
        self::assertEquals(1, $collection->count());
    }

}
