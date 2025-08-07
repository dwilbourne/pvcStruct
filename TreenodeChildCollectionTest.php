<?php

namespace pvcTests\struct\unit_tests\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\node\TreenodeChildCollection;
use PHPUnit\Framework\TestCase;

class TreenodeChildCollectionTest extends TestCase
{
    protected TreenodeChildCollection $treenodeChildCollection;

    public function setUp() : void
    {
        $this->treenodeChildCollection = new TreenodeChildCollection();
    }

    /**
     * @return void
     * @throws \pvc\struct\collection\err\InvalidKeyException
     * @covers \pvc\struct\tree\node\TreenodeChildCollection::rewind
     */
    public function testIteration(): void
    {
        $node1 = $this->createMock(TreenodeInterface::class);
        $node2 = $this->createMock(TreenodeInterface::class);
        $node3 = $this->createMock(TreenodeInterface::class);
        $this->treenodeChildCollection->add(1, $node1);
        $this->treenodeChildCollection->add(2, $node2);
        $this->treenodeChildCollection->add(3, $node3);

        $i = 0;
        foreach ($this->treenodeChildCollection as $node) {
            $i++;
        }
        self::assertEquals(3, $i);
    }
}
