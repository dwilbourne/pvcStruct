<?php

namespace pvcTests\struct\unit_tests\tree\tree;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\tree\TreeOrdered;

class TreeOrderedTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreeOrdered
     */
    protected TreeOrdered $tree;

    /**
     * @var TreenodeFactoryOrdered<TreenodeOrdered, TreeOrdered, CollectionOrdered>&MockObject
     */
    protected TreenodeFactoryOrdered&MockObject $nodeFactory;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->nodeFactory = $this->createMock(TreenodeFactoryOrdered::class);
        $this->tree = new TreeOrdered($this->nodeFactory);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\tree\TreeOrdered::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreeOrdered::class, $this->tree);
    }

}
