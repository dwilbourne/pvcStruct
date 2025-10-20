<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\err\ComparatorException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvc\struct\collection\IndexedCollection;
use pvcTests\struct\unit_tests\collection\fixtures\IndexedElement;

/**
 *
 */
class IndexedCollectionTest extends TestCase
{
    /**
     * @var IndexedCollection<non-negative-int, IndexedElement>
     */
    protected IndexedCollection $collection;

    /**
     * @var array<non-negative-int, IndexedElement>
     */
    protected array $elementArray;

    public function setUp(): void
    {
        $iterator = new ArrayIterator();
        $this->collection = new IndexedCollection($iterator);
    }

    /**
     * @return void
     * @throws ComparatorException
     * @covers \pvc\struct\collection\IndexedCollection::setComparator
     */
    public function testSetComparatorThrowsException(): void
    {
        $iterator = new ArrayIterator();
        self::expectException(ComparatorException::class);
        $this->collection = new IndexedCollection($iterator);
        $this->collection->setComparator(null);
    }
    /**
     * @param  non-negative-int  $n
     *
     * @return void
     */
    protected function addElements(int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            $this->elementArray[$i] = new IndexedElement();
            /**
             * start with 'normal' indexes, e.g. 0 based ascending order
             */
            $this->elementArray[$i]->setIndex($i);
            $this->collection->add($this->elementArray[$i], $i);
        }
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(IndexedCollection::class, $this->collection);
    }

    /**
     * testAddPutsIndexAtEndOfListIfElementIndexNotAlreadySet
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::add
     * @covers \pvc\struct\collection\IndexedCollection::trimIndex
     * @covers \pvc\struct\collection\IndexedCollection::shuffleIndices
     */
    public function testAddPutsIndexAtEndOfListIfElementIndexNotAlreadySet(): void
    {
        /**
         * newly created elements have nothing set in the index property
         */
        $elementA = new IndexedElement();
        $this->collection->add($elementA, 3);
        self::assertEquals(0, $elementA->getIndex());

        $elementB = new IndexedElement();
        $this->collection->add($elementB, 5);
        self::assertEquals(1, $elementB->getIndex());

        self::assertEquals(0, $elementA->getIndex());
        self::assertEquals(1, $elementB->getIndex());
    }

    /**
     * testAddUsesElementsExistingIndexes
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::add
     * @covers \pvc\struct\collection\IndexedCollection::trimIndex
     * @covers \pvc\struct\collection\IndexedCollection::shuffleIndices
     */
    public function testAddUsesElementsExistingIndexes(): void
    {
        $elementA = new IndexedElement();
        $this->collection->add($elementA, 3);
        self::assertEquals(0, $elementA->getIndex());

        $elementB = new IndexedElement();
        $elementB->setIndex(5);
        $this->collection->add($elementB, 5);
        /**
         * index is trimmed to the appropriate value
         */
        self::assertEquals(1, $elementB->getIndex());

        $elementC = new IndexedElement();
        $elementC->setIndex(1);
        $this->collection->add($elementC, 7);

        /**
         * ElementB gets pushed to index = 2
         */
        self::assertEquals(0, $elementA->getIndex());
        self::assertEquals(1, $elementC->getIndex());
        self::assertEquals(2, $elementB->getIndex());
    }

    /**
     * @return array<non-negative-int>
     */
    protected function getArrayOfIndexesByKey(): array
    {
        $result = [];
        foreach ($this->collection->getElements() as $key => $element) {
            $result[$key] = $element->getIndex();
        }
        return $result;
    }

    /**
     * @covers \pvc\struct\collection\IndexedCollection::delete
     * @covers \pvc\struct\collection\IndexedCollection::add
     * @covers \pvc\struct\collection\IndexedCollection::trimIndex
     * @covers \pvc\struct\collection\IndexedCollection::shuffleIndices
     */
    public function testDeleteThenAddInMiddle(): void
    {
        $this->addElements(3);

        self::assertEquals(3, $this->collection->count());

        $this->collection->delete(1);
        $expectedIndices = [0, 1];
        $actualIndices = array_values($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedIndices, $actualIndices);

        $element = new IndexedElement();
        /**
         * place this element to be second in the list
         */
        $element->setIndex(1);
        $newKey = 5;
        $this->collection->add($element, $newKey);

        /**
         * the add method reorders the internal array so it is ascending by index
         */
        $expectedKeys = [0, $newKey, 2];
        $actualKeys = array_keys($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedKeys, $actualKeys);

        $expectedIndices = [0, 1, 2];
        $actualIndices = array_values($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedIndices, $actualIndices);

        /**
         * test that the element just added is, in fact, the 'second' in the list and it did not get moved
         * for some reason
         */
        self::assertEquals(
            1,
            $this->collection->getElement($newKey)->getIndex()
        );
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::update
     */
    public function testUpdate(): void
    {
        $this->addElements(3);

        /**
         * create a new element and have it replace the first element in the collection
         */
        $newElement = new IndexedElement();
        $newElement->setIndex(0);

        /**
         * replace the element with $key = 2.  Note that the key does not change but the value of
         * the element does
         */
        $this->collection->update(2, $newElement);
        $expectedKeys = [2, 0, 1];
        $actualKeys = array_keys($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedKeys, $actualKeys);
        self::assertEquals($newElement, $this->collection->getElement(2));
    }

    /**
     * testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist
     *
     * @covers \pvc\struct\collection\IndexedCollection::setIndex
     */
    public function testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist(
    ): void
    {
        $this->addElements(6);
        self::expectException(NonExistentKeyException::class);
        $this->collection->setIndex(10, 4);
    }

    /**
     * testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex
     *
     * @covers \pvc\struct\collection\IndexedCollection::setIndex
     * @covers \pvc\struct\collection\IndexedCollection::getIndex
     */
    public function testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex(
    ): void
    {
        $this->addElements(3);

        $key = 1;
        $proposedNewIndex = 10;
        $expectedNewIndex = 2;

        $this->collection->setIndex($key, $proposedNewIndex);
        self::assertEquals(
            $expectedNewIndex,
            $this->collection->getIndex($key)
        );
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     * @covers \pvc\struct\collection\IndexedCollection::getIndex
     */
    public function testGetIndexThrowsExceptionWithNonExistentKey(): void
    {
        $key = 7;
        $this->addElements(3);
        self::expectException(NonExistentKeyException::class);
        $this->collection->getIndex($key);
    }

    /**
     * testGetIndexThrowsExceptionWithInvalidKey
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::getIndex
     */
    public function testGetIndexThrowsExceptionWithInvalidKey(): void
    {
        $key = 1;
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($key)->willReturn(false);
        $this->collection->keyTester = $keyTester;
        self::expectException(InvalidKeyException::class);
        $this->collection->getIndex($key);
    }

    /**
     * testGetIndex
     * @return void
     * @covers \pvc\struct\collection\IndexedCollection::getIndex
     */
    public function testGetIndex(): void
    {
        $this->addElements(3);
        self::assertEquals(1, $this->collection->getIndex(1));
    }
}
