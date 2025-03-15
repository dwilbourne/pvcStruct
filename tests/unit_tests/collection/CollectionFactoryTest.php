<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use stdClass;

class CollectionFactoryTest extends TestCase
{
    protected CollectionFactory $collectionFactory;

    public function setUp() : void
    {
        $this->collectionFactory = new CollectionFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionFactory::makeCollection
     */
    public function testMakeCollectionWithEmptyArray(): void
    {
        $collection = $this->collectionFactory->makeCollection();
        self::assertInstanceOf(Collection::class, $collection);
        self::assertTrue($collection->isEmpty());
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionFactory::makeCollection
     */
    public function testMakeCollectionWithArray(): void
    {
        $collection = $this->collectionFactory->makeCollection([new stdClass()]);
        self::assertInstanceOf(Collection::class, $collection);
        self::assertEquals(1, $collection->count());
    }
}
