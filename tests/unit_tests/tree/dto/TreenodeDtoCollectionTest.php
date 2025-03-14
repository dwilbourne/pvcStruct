<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\struct\tree\dto\TreenodeDtoCollection;

class TreenodeDtoCollectionTest extends TestCase
{
    protected TreenodeDtoCollection $treenodeCollection;

    protected CollectionInterface $collection;

    public function setUp() : void
    {
        $this->collection = $this->createMock(CollectionInterface::class);
        $this->treenodeCollection = new TreenodeDtoCollection($this->collection);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoCollection::__construct
     */
    public function testConstruction(): void
    {
        self::assertInstanceOf(TreenodeDtoCollection::class, $this->treenodeCollection);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoCollection::count
     */
    public function testCountable(): void
    {
        $this->collection->expects($this->once())->method('count');
        $n = $this->treenodeCollection->count();
        unset($n);
    }
}
