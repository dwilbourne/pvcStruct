<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeFactory;

class TreenodeFactoryTest extends TestCase
{

    protected TreenodeChildCollectionFactoryInterface&MockObject $collectionFactory;

    /**
     * @var TreenodeFactoryInterface
     */
    protected TreenodeFactoryInterface $factory;

    public function setUp(): void
    {
        $this->collectionFactory = $this->createMock(
            TreenodeChildCollectionFactoryInterface::class
        );
        $this->factory = new TreenodeFactory($this->collectionFactory);
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
     * testMakeNode
     *
     * @covers \pvc\struct\tree\node\TreenodeFactory::makeNode
     */
    public function testMakeNode(): void
    {
        $collectionMock = $this->createMock(TreenodeChildCollectionInterface::class);
        $this->collectionFactory
            ->expects(self::once())
            ->method('makeChildCollection')
            ->willReturn($collectionMock);
        $node = $this->factory->makeNode();
        self::assertInstanceOf(Treenode::class, $node);
    }
}
