<?php

namespace pvcExamples\struct\tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrderedByIndex;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\InvalidValueException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\tree\node\Treenode;
use pvcExamples\struct\ordered\TreenodeOrdered;
use pvcExamples\struct\ordered\TreeOrdered;
use pvcTests\struct\unit_tests\tree\node\fixture\TreenodeTestingFixture;

class TreenodeOrderedTest extends TestCase
{
    /**
     * @var TreenodeTestingFixture
     */
    protected TreenodeTestingFixture $fixture;

    /**
     * @var CollectionOrderedByIndex&MockObject
     */
    protected CollectionOrderedByIndex&MockObject $collection;

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
        $this->collection = $this->createMock(CollectionOrderedByIndex::class);
        $this->tree = $this->createMock(TreeOrdered::class);
        $this->tree->method('getTreeId')->willReturn($this->fixture->treeId);
    }

    /**
     * testConstruct
     *
     * @covers \pvcExamples\struct\ordered\TreenodeOrdered::__construct
     */
    public function testConstruct(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        self::assertInstanceOf(TreenodeOrdered::class, $node);
    }

    /**
     * @return void
     * @covers \pvcExamples\struct\ordered\TreenodeOrdered::setIndex
     * @covers \pvcExamples\struct\ordered\TreenodeOrdered::getIndex
     */
    public function testSetGetIndex(): void
    {
        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        $testIndex = 8;
        $node->setIndex($testIndex);
        self::assertEquals($testIndex, $node->getIndex());
    }


    /**
     * @return void
     * @throws AlreadySetNodeidException
     * @throws ChildCollectionException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws InvalidValueException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeException
     * @covers \pvcExamples\struct\ordered\TreenodeOrdered::hydrate
     */
    public function testHydrateSetsIndex(): void
    {
        $nodeId = 2;
        $parentId = null;
        $index = 3;
        $dto = $this->fixture->makeDTOOrdered($nodeId, $parentId, $index);

        $this->collection->expects($this->once())->method('isEmpty')
            ->willReturn(true);
        $node = new TreenodeOrdered($this->collection, $this->tree);
        $node->hydrate($dto);
        self::assertEquals($index, $node->getIndex());
    }
}
