<?php

namespace pvcExamples\struct\tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvcExamples\struct\unordered\TreenodeFactoryUnordered;
use pvcExamples\struct\unordered\TreenodeBaseUnordered;
use pvcExamples\struct\unordered\TreeUnordered;

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
     * @var TreenodeFactoryUnordered<TreenodeBaseUnordered, TreeUnordered, Collection>&MockObject
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
     * @covers \pvcExamples\struct\unordered\TreeUnordered::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreeUnordered::class, $this->tree);
    }

}
