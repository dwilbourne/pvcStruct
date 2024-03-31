<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;

class TreenodeOrderedFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->tree = $this->createMock(TreeOrderedInterface::class);
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->collectionFactory = $this->createMock(CollectionOrderedFactory::class);
        $this->validator = $this->createMock(PayloadTesterInterface::class);


        $this->treenodeOrderedFactory = new TreenodeOrderedFactory($this->collectionFactory, $this->validator);
        $this->treenodeOrderedFactory->setTree($this->tree);
    }

    /**
     * testMakeNode
     * @covers \pvc\struct\tree\node\factory\TreenodeOrderedFactory::makeNode
     */
    public function testMakeNode(): void
    {
        /** @var CollectionOrdered|MockObject $mockCollection */
        $mockCollection = $this->createMock(CollectionOrdered::class);
        $mockCollection->method('isEmpty')->willReturn(true);
        $this->collectionFactory->expects($this->once())->method('makeCollection')->willReturn($mockCollection);
        self::assertInstanceOf(TreenodeOrderedInterface::class, $this->treenodeOrderedFactory->makeNode());
    }
}
