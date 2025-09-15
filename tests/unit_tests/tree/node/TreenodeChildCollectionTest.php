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
        $this->treenodeChildCollection->add($node1, 1);
        $this->treenodeChildCollection->add($node2, 2);
        $this->treenodeChildCollection->add($node3, 3);

        $i = 0;
        foreach ($this->treenodeChildCollection as $node) {
            $i++;
        }
        self::assertEquals(3, $i);
    }
}
