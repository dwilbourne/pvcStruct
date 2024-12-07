<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;

class TreenodeOrderedFactoryTest extends TestCase
{
    protected TreenodeOrderedFactory $treenodeOrderedFactory;

    protected CollectionFactoryInterface|MockObject $collectionFactory;

    public function setUp(): void
    {
        $treeId = 0;
        $tree = $this->createMock(TreeOrderedInterface::class);
        $tree->method('getTreeId')->willReturn($treeId);

        $this->collectionFactory = $this->createMock(CollectionOrderedFactory::class);
        $validator = $this->createMock(PayloadTesterInterface::class);


        $this->treenodeOrderedFactory = new TreenodeOrderedFactory($this->collectionFactory, $validator);
        $this->treenodeOrderedFactory->setTree($tree);
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
