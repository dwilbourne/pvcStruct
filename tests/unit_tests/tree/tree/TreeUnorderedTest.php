<?php

namespace pvcTests\struct\unit_tests\tree\tree;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\node\TreenodeUnordered;
use pvc\struct\tree\tree\TreeUnordered;

class TreeUnorderedTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreeUnordered
     */
    protected TreeUnordered $tree;

    /**
     * @var TreenodeFactoryUnordered<TreenodeUnordered, TreeUnordered, Collection>&MockObject
     */
    protected TreenodeFactoryUnordered&MockObject $nodeFactory;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->treeId = 0;
        $this->nodeFactory = $this->createMock(TreenodeFactoryUnordered::class);
        $this->tree = new TreeUnordered($this->nodeFactory);
    }

    /**
     * testConstruct
     *
     * @covers \pvc\struct\tree\tree\TreeUnordered::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreeUnordered::class, $this->tree);
    }

}
