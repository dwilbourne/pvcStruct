<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrderedByIndex;
use pvc\struct\collection\err\InvalidComparatorException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvcTests\struct\unit_tests\collection\fixtures\IndexedElement;
use pvcTests\struct\unit_tests\collection\fixtures\IndexedElementFactory;

/**
 *
 */
class CollectionOrderedByIndexTest extends TestCase
{
    /**
     * @var CollectionOrderedByIndex<IndexedElement>
     */
    protected CollectionOrderedByIndex $collection;

    protected IndexedElementFactory $elementFactory;

    /**
     * @var array<IndexedElement>
     */
    protected array $elementArray;

    public function setUp(): void
    {
        $this->elementFactory = new IndexedElementFactory();
    }

    /**
     * @return void
     * @throws InvalidComparatorException
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::setComparator
     */
    public function testSetComparatorThrowsException(): void
    {
        self::expectException(InvalidComparatorException::class);
        $this->collection = new CollectionOrderedByIndex();
        $this->collection->setComparator(null);
    }
    /**
     * @param  non-negative-int  $n
     *
     * @return void
     */
    protected function addElements(int $n): void
    {
        $this->elementArray = $this->elementFactory->makeElementArray($n);
        $this->collection = new CollectionOrderedByIndex($this->elementArray);
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::__construct
     */
    public function testConstruct(): void
    {
        $collection = new CollectionOrderedByIndex();
        self::assertInstanceOf(CollectionOrderedByIndex::class, $collection);
    }


    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::__construct
     */
    public function testSetOrderedtoTrueReindexesElements(): void
    {
        /**
         * collection element factory makes non-negative integer indices in each element, but they are not sequential
         */
        $this->addElements(4);
        $expectedIndices = [0, 1, 2, 3];
        $actualIndices = array_values($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedIndices, $actualIndices);
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
     * testCrud operations
     *
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::delete
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::add
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::trimIndex
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::shuffleIndices
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
        $this->collection->add($newKey, $element);

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
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::update
     */
    public function testUpdate(): void
    {
        $indexed = true;
        $this->addElements(3);

        /**
         * create a new element and have it replace the first element in the collection
         */
        $newElement = $this->elementFactory->makeIndexedElement('foo', 0);

        /**
         * replace the existing element with $key = 2.  Note that the key does not change but the value of
         * the element does
         */
        $this->collection->update(2, $newElement);
        $expectedKeys = [2, 0, 1];
        $actualKeys = array_keys($this->getArrayOfIndexesByKey());
        self::assertEquals($expectedKeys, $actualKeys);
        self::assertEquals($newElement, $this->collection->getElement(2));
    }

    /**
     * testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection
     * this test also confirms that the shuffle method does nothing if the count of the collection is less than 2
     *
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::add
     */
    public function testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection(
    ): void
    {
        $element = new IndexedElement();
        $key = 10;
        $proposedNewKeyIndex = 5;
        $element->setIndex($proposedNewKeyIndex);
        $expectedNewKeyIndex = 0;

        $this->collection = new CollectionOrderedByIndex();
        self::assertTrue($this->collection->isEmpty());
        $this->collection->add($key, $element);
        self::assertEquals(
            $expectedNewKeyIndex,
            $this->collection->getElement($key)->getIndex()
        );
    }

    /**
     * testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist
     *
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::setIndex
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
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::setIndex
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::getIndex
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
     * @covers \pvc\struct\collection\CollectionOrderedByIndex::getIndex
     */
    public function testGetIndexThrowsExceptionWithNonExistentKey(): void
    {
        $key = 7;
        $this->addElements(3);
        self::expectException(InvalidKeyException::class);
        $this->collection->getIndex($key);
    }
}
