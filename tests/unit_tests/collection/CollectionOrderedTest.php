<?php

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvcTests\struct\unit_tests\collection\fixtures\CollectionElement;
use pvcTests\struct\unit_tests\collection\fixtures\CollectionElementFactory;
use pvcTests\struct\unit_tests\collection\fixtures\CollectionIndexedElement;

class CollectionOrderedTest extends TestCase
{
    /**
     * @var CollectionOrdered<CollectionElement>
     */
    protected CollectionOrdered $collection;

    protected CollectionElementFactory $collectionElementFactory;

    protected array $collectionElements;

    public function setUp(): void
    {
        $this->collectionElementFactory = new CollectionElementFactory();
    }

    /**
     * @param non-negative-int $n
     * @return void
     */
    protected function addElements(int $n): void
    {
        $indexed = true;
        $this->collectionElements = $this->collectionElementFactory->makeCollectionElementArray($n, $indexed);
        $this->collection = new CollectionOrdered($this->collectionElements);
    }

    protected function getResultArray(): array
    {
        $result = [];
        foreach ($this->collection->getElements() as $key => $element) {
            $result[$key] = $element->getIndex();
        }
        return $result;
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::__construct
     */
    public function testConstructReindexesElements(): void
    {
        /**
         * collection element factory makes non-negative integer indices in each element, but they are not sequential
         */
        $this->addElements(4);
        $expectedIndices = [0, 1, 2, 3];
        $actualIndices = array_values($this->getResultArray());
        self::assertEquals($expectedIndices, $actualIndices);
    }

    /**
     * testCrud operations
     *
     * @covers \pvc\struct\collection\CollectionOrdered::delete
     * @covers \pvc\struct\collection\CollectionOrdered::add
     * @covers \pvc\struct\collection\CollectionOrdered::trimIndex
     * @covers \pvc\struct\collection\CollectionOrdered::shuffleIndices
     * @covers \pvc\struct\collection\CollectionOrdered::compareIndices
     */
    public function testDeleteThenAddInMiddle(): void
    {
        $this->addElements(3);

        self::assertEquals(3, $this->collection->count());

        $this->collection->delete(1);
        $expectedIndices = [0, 1];
        $actualIndices = array_values($this->getResultArray());
        self::assertEquals($expectedIndices, $actualIndices);

        $element = new CollectionIndexedElement();
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
        $actualKeys = array_keys($this->getResultArray());
        self::assertEquals($expectedKeys, $actualKeys);

        $expectedIndices = [0, 1, 2];
        $actualIndices = array_values($this->getResultArray());
        self::assertEquals($expectedIndices, $actualIndices);

        /**
         * test that the element just added is, in fact, the 'second' in the list and it did not get moved
         * for some reason
         */
        self::assertEquals(1, $this->collection->getElement($newKey)->getIndex());
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\CollectionOrdered::update
     */
    public function testUpdate(): void
    {
        $indexed = true;
        $this->addElements(3);
        /**
         * create a new element and have it replace the first element in the collection
         */
        $newElement = $this->collectionElementFactory->makeElement(7, $indexed);
        $newElement->setIndex(0);
        /**
         * replace the existing element with $key = 2.  Note that the key does not change but the value of
         * the element does
         */
        $this->collection->update(2, $newElement);
        $expectedKeys = [2, 0, 1];
        $actualKeys = array_keys($this->getResultArray());
        self::assertEquals($expectedKeys, $actualKeys);
        self::assertEquals($newElement, $this->collection->getElement(2));
    }

    /**
     * testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection
     * this test also confirms that the shuffle method does nothing if the count of the collection is less than 2
     *
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::add
     */
    public function testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection(): void
    {
        $element = new CollectionIndexedElement();
        $key = 10;
        $proposedNewKeyIndex = 5;
        $element->setIndex($proposedNewKeyIndex);
        $expectedNewKeyIndex = 0;

        $this->collection = new CollectionOrdered();
        self::assertTrue($this->collection->isEmpty());
        $this->collection->add($key, $element);
        self::assertEquals($expectedNewKeyIndex, $this->collection->getElement($key)->getIndex());
    }

    /**
     * testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist
     *
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     */
    public function testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist(): void
    {
        $this->addElements(6);
        self::expectException(NonExistentKeyException::class);
        $this->collection->setIndex(10, 4);
    }

    /**
     * testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex
     *
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     * @covers \pvc\struct\collection\CollectionOrdered::getIndex
     */
    public function testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex(): void
    {
        $this->addElements(3);

        $key = 1;
        $proposedNewIndex = 10;
        $expectedNewIndex = 2;

        $this->collection->setIndex($key, $proposedNewIndex);
        self::assertEquals($expectedNewIndex, $this->collection->getIndex($key));
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::getIndex
     */
    public function testGetIndexThrowsExceptionWithInvalidKey(): void
    {
        $key = -2;
        $this->addElements(3);
        self::expectException(InvalidKeyException::class);
        $this->collection->getIndex($key);
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::getIndex
     */
    public function testGetIndexThrowsExceptionWithNonExistentKey(): void
    {
        $key = 7;
        $this->addElements(3);
        self::expectException(NonExistentKeyException::class);
        $this->collection->getIndex($key);
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::getFirst
     * @covers \pvc\struct\collection\CollectionOrdered::getLast
     * @covers \pvc\struct\collection\CollectionOrdered::getNth
     */
    public function testGetFirstLastNthElement(): void
    {
        $indexed = true;

        $key = 7;
        $a = $this->collectionElementFactory->makeElement($key, $indexed);

        $key = 10;
        $b = $this->collectionElementFactory->makeElement($key, $indexed);

        $key = 10;
        $c = $this->collectionElementFactory->makeElement($key, $indexed);

        $this->collection = new CollectionOrdered([$a, $b, $c]);

        self::assertSame($a, $this->collection->getFirst());
        self::assertSame($c, $this->collection->getLast());
        self::assertSame($b, $this->collection->getNth(1));
        self::assertSame($c, $this->collection->getNth(2));
        self::assertSame($c, $this->collection->getNth(4));
    }
}
