<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\struct\tree\node\TreenodeUnordered;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixtureUnordered;

class TreenodeUnorderedTest extends TestCase
{

    public function setUp(): void
    {
        $this->fixture = new TreenodeTestingFixtureUnordered();
    }

    /**
     * testConstruct
     * @throws \pvc\struct\tree\err\InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeUnordered::__construct
     */
    public function testConstruct(): void
    {
        $nodeId = 0;
        $parentId = null;
        $treeId = 0;
        $tree = $this->createMock(TreeUnorderedInterface::class);
        $children = $this->createMock(CollectionUnorderedInterface::class);
        $children->method('isEmpty')->willReturn(true);
        $node = new TreenodeUnordered($nodeId, $parentId, $treeId, $tree, $children);
        self::assertInstanceOf(TreenodeUnordered::class, $node);
    }
}
