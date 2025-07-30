<?php

namespace pvcExamples\struct\tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvcExamples\struct\ordered\TreenodeFactoryOrdered;
use pvcExamples\struct\ordered\TreenodeOrdered;
use pvcExamples\struct\ordered\TreeOrdered;

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
     * @covers \pvcExamples\struct\ordered\TreeOrdered::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreeOrdered::class, $this->tree);
    }

}
