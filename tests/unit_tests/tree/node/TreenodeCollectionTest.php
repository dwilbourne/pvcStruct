<?php

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\node\TreenodeCollection;

/**
 * @template PayloadType
 */
class TreenodeCollectionTest extends TestCase
{
    /**
     * @var TreenodeCollection<PayloadType>
     */
    protected TreenodeCollection $treenodeCollection;

    /**
     * @var MockObject&CollectionInterface<TreenodeInterface<PayloadType>>
     */
    protected CollectionInterface $collection;

    public function setUp() : void
    {
        $this->collection = $this->createMock(CollectionInterface::class);
        $this->treenodeCollection = new TreenodeCollection($this->collection);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::__construct
     */
    public function testConstruct() : void
    {
        self::assertInstanceOf(TreenodeCollection::class, $this->treenodeCollection);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::count
     */
    public function testCount() : void
    {
        $this->collection->expects($this->once())->method('count');
        $this->treenodeCollection->count();
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::add
     */
    public function testAdd() : void
    {
        $key = 1;
        $treenode = $this->createMock(TreenodeInterface::class);
        $this->collection->expects($this->once())->method('add')->with($key, $treenode);
        $this->treenodeCollection->add($key, $treenode);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::delete
     */
    public function testDelete() : void
    {
        $key = 1;
        $this->collection->expects($this->once())->method('delete')->with($key);
        $this->treenodeCollection->delete($key);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::getKey
     */
    public function testGetKey() : void
    {
        $treenode = $this->createMock(TreenodeInterface::class);
        $this->collection->expects($this->once())->method('getKey')->with($treenode);
        $this->treenodeCollection->getKey($treenode);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::isEmpty
     */
    public function testIsEmpty() : void
    {
        $this->collection->expects($this->once())->method('isEmpty');
        $this->treenodeCollection->isEmpty();
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::getElements
     */
    public function testGetElements() : void
    {
        $this->collection->expects($this->once())->method('getElements');
        $this->treenodeCollection->getElements();
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::getIndex
     */
    public function testGetIndex() : void
    {
        $key = 1;
        $this->collection->expects($this->once())->method('getIndex')->with($key);
        $this->treenodeCollection->getIndex($key);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollection::setIndex
     */
    public function testSetIndex() : void
    {
        $key = 1;
        $index = 5;
        $this->collection->expects($this->once())->method('setIndex')->with($key, $index);
        $this->treenodeCollection->setIndex($key, $index);
    }



}
