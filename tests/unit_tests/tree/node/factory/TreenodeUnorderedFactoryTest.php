<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\struct\collection\CollectionUnordered;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;

class TreenodeUnorderedFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->tree = $this->createMock(TreeUnorderedInterface::class);
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->collectionFactory = $this->createMock(CollectionUnorderedFactory::class);
        $this->validator = $this->createMock(PayloadTesterInterface::class);


        $this->treenodeOrderedFactory = new TreenodeUnorderedFactory($this->collectionFactory, $this->validator);
        $this->treenodeOrderedFactory->setTree($this->tree);
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
        self::assertInstanceOf(TreenodeUnorderedInterface::class, $this->treenodeOrderedFactory->makeNode());
    }


}
