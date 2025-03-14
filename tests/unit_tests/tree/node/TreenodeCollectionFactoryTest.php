<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\struct\tree\node\TreenodeCollection;
use pvc\struct\tree\node\TreenodeCollectionFactory;

class TreenodeCollectionFactoryTest extends TestCase
{
    protected TreenodeCollectionFactory $treenodeCollectionFactory;

    protected CollectionFactoryInterface $collectionFactory;

    public function setUp() : void
    {
        $this->collectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->treenodeCollectionFactory = new TreenodeCollectionFactory($this->collectionFactory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollectionFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeCollectionFactory::class, $this->treenodeCollectionFactory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollectionFactory::makeTreenodeCollection
     */
    public function testMakeTreenodeCollection() : void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $this->collectionFactory->expects(self::once())->method('makeCollection')->willReturn($collection);
        self::assertInstanceOf(TreenodeCollection::class, $this->treenodeCollectionFactory->makeTreenodeCollection());
    }
}
