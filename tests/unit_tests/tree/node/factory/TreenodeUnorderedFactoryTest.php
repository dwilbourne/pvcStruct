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
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\struct\collection\CollectionUnordered;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;

class TreenodeUnorderedFactoryTest extends TestCase
{
    protected TreenodeUnorderedFactory $treenodeUnorderedFactory;

    protected CollectionFactoryInterface $collectionFactory;


    public function setUp(): void
    {
        $treeId = 0;
        $tree = $this->createMock(TreeUnorderedInterface::class);
        $tree->method('getTreeId')->willReturn($treeId);
        $this->collectionFactory = $this->createMock(CollectionUnorderedFactory::class);
        $validator = $this->createMock(PayloadTesterInterface::class);


        $this->treenodeUnorderedFactory = new TreenodeUnorderedFactory($this->collectionFactory, $validator);
        $this->treenodeUnorderedFactory->setTree($tree);
    }

    /**
     * testMakeNode
     * @covers \pvc\struct\tree\node\factory\TreenodeUnorderedFactory::makeNode
     */
    public function testMakeNode(): void
    {
        /** @var CollectionUnordered|MockObject $mockCollection */
        $mockCollection = $this->createMock(CollectionUnordered::class);
        $mockCollection->method('isEmpty')->willReturn(true);
        $this->collectionFactory->expects($this->once())->method('makeCollection')->willReturn($mockCollection);
        self::assertInstanceOf(TreenodeUnorderedInterface::class, $this->treenodeUnorderedFactory->makeNode());
    }


}
