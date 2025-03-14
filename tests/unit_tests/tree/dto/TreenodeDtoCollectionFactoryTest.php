<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\struct\tree\dto\TreenodeDtoCollection;
use pvc\struct\tree\dto\TreenodeDtoCollectionFactory;

class TreenodeDtoCollectionFactoryTest extends TestCase
{
    protected TreenodeDtoCollectionFactory $treenodeDtoCollectionFactory;

    protected CollectionFactoryInterface $collectionFactory;

    public function setUp() : void
    {
        $this->collectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->treenodeDtoCollectionFactory = new TreenodeDtoCollectionFactory($this->collectionFactory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoCollectionFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeDtoCollectionFactory::class, $this->treenodeDtoCollectionFactory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoCollectionFactory::makeTreenodeDtoCollection
     */
    public function testMakeTtreenodeDtoCollection(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $this->collectionFactory->method('makeCollection')->willReturn($collection);
        self::assertInstanceOf(TreenodeDtoCollection::class, $this->treenodeDtoCollectionFactory->makeTreenodeDtoCollection());
    }
}
