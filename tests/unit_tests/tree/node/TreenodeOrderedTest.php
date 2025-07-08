<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\tree\TreeOrdered;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixture;

class TreenodeOrderedTest extends TestCase
{
    /**
     * @var TreenodeTestingFixture
     */
    protected TreenodeTestingFixture $fixture;

    /**
     * @var CollectionOrdered&MockObject
     */
    protected CollectionOrdered&MockObject $collection;

    /**
     * @var TreeOrdered&MockObject
     */
    protected TreeOrdered&MockObject $tree;

    /**
     * @var Treenode
     */
    protected TreenodeOrdered $node;

    public function setUp(): void
    {
        $this->fixture = new TreenodeTestingFixture();
        $this->fixture->setUp();
        $this->collection = $this->createMock(CollectionOrdered::class);
        $this->tree = $this->createMock(TreeOrdered::class);
        $this->tree->method('getTreeId')->willReturn($this->fixture->treeId);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\node\TreenodeOrdered::__construct
     */
    public function testConstruct(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        self::assertInstanceOf(TreenodeOrdered::class, $node);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testSetGetIndex(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        $testIndex = 8;
        $node->setIndex($testIndex);
        self::assertEquals($testIndex, $node->getIndex());
    }


    /**
     * @return void
     * @throws \pvc\struct\tree\err\AlreadySetNodeidException
     * @throws \pvc\struct\tree\err\ChildCollectionException
     * @throws \pvc\struct\tree\err\CircularGraphException
     * @throws \pvc\struct\tree\err\InvalidNodeIdException
     * @throws \pvc\struct\tree\err\InvalidParentNodeException
     * @throws \pvc\struct\tree\err\InvalidValueException
     * @throws \pvc\struct\tree\err\NodeNotEmptyHydrationException
     * @throws \pvc\struct\tree\err\RootCannotBeMovedException
     * @throws \pvc\struct\tree\err\SetTreeIdException
     * @covers \pvc\struct\tree\node\TreenodeOrdered::hydrate
     */
    public function testHydrateSetsIndex(): void
    {
        $nodeId = 2;
        $parentId = null;
        $index = 3;
        $dto = $this->fixture->makeDTOOrdered($nodeId, $parentId, $index);

        $this->collection->expects($this->once())->method('isEmpty')->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        $node->hydrate($dto);
        self::assertEquals($index, $node->getIndex());
    }
}
