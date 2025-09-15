<?php

namespace pvcTests\struct\unit_tests\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\tree\TreenodeCollection;
use PHPUnit\Framework\TestCase;

class TreenodeCollectionTest extends TestCase
{
    protected TreenodeCollection $treenodeCollection;

    public function setUp() : void
    {
        $this->treenodeCollection = new TreenodeCollection();
    }

    /**
     * @return void
     * @throws \pvc\struct\collection\err\InvalidKeyException
     * @coversNothing
     */
    public function testIteration(): void
    {
        $node1 = $this->createMock(TreenodeInterface::class);
        $node2 = $this->createMock(TreenodeInterface::class);
        $node3 = $this->createMock(TreenodeInterface::class);
        $this->treenodeCollection->add($node1, 1);
        $this->treenodeCollection->add($node2, 2);
        $this->treenodeCollection->add($node3, 3);

        $i = 0;
        foreach ($this->treenodeCollection as $node) {
            $i++;
        }
        self::assertEquals(3, $i);
    }

}
