<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\struct\tree\node\TreenodeFactory;

class TreenodeFactoryTest extends TestCase
{

    protected CollectionFactoryInterface&MockObject $collectionFactory;

    /**
     * @var TreenodeFactoryInterface
     */
    protected TreenodeFactoryInterface $factory;

    public function setUp(): void
    {
        $this->collectionFactory = $this->createMock(
            CollectionFactoryInterface::class
        );
        $this->factory = $this->getMockBuilder(TreenodeFactory::class)
            ->setConstructorArgs([$this->collectionFactory])
            ->getMockForAbstractClass();
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\node\TreenodeFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeFactory::class, $this->factory);
    }

    /**
     * testGetCollectionFactory
     *
     * @covers \pvc\struct\tree\node\TreenodeFactory::makeCollection
     */
    public function testMakeCollection(): void
    {
        $collectionMock = $this->createMock(CollectionInterface::class);
        $this->collectionFactory
            ->expects(self::once())
            ->method('makeCollection')
            ->willReturn($collectionMock);
        $this->factory->makeCollection();
    }
}
