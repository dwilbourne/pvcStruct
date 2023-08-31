<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\node\TreenodeOrdered;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixtureOrdered;

class TreenodeOrderedTest extends TestCase
{
    protected TreenodeTestingFixtureOrdered $fixture;

    public function setUp(): void
    {
        $this->fixture = new TreenodeTestingFixtureOrdered();
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeOrdered::__construct
     */
    public function testConstruct(): void
    {
        $nodeId = 0;
        $parentId = null;
        $treeId = 0;
        $index = 0;

        $tree = $this->createMock(TreeOrderedInterface::class);
        $tree->method('rootTest')->willReturn(true);

        $siblings = $this->createMock(CollectionOrderedInterface::class);
        $siblings->expects($this->once())->method('setIndex');
        $siblings->method('getKey')->willReturn(0);
        $tree->method('makeCollection')->willReturn($siblings);

        $children = $this->createMock(CollectionOrderedInterface::class);
        $children->method('isEmpty')->willReturn(true);

        $node = new TreenodeOrdered($nodeId, $parentId, $treeId, $index, $tree, $children);

        self::assertInstanceOf(TreenodeOrdered::class, $node);
    }
}
