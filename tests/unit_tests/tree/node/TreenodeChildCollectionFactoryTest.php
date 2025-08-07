<?php

namespace pvcTests\struct\unit_tests\tree\node;

use pvc\struct\tree\node\TreenodeChildCollection;
use pvc\struct\tree\node\TreenodeChildCollectionFactory;
use PHPUnit\Framework\TestCase;

class TreenodeChildCollectionFactoryTest extends TestCase
{
    protected TreenodeChildCollectionFactory $factory;

    public function setUp() : void
    {
        $this->factory = new TreenodeChildCollectionFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeChildCollectionFactory::makeChildCollection
     */
    public function testMakeChildCollection() : void
    {
        $collection = $this->factory->makeChildCollection();
        self::assertInstanceOf(TreenodeChildCollection::class, $collection);
    }
}
