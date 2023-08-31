<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

class CollectionOrderedTest extends TestCase
{
    use CollectionTestingTrait;

    /**
     * @var CollectionOrdered<string>
     */
    protected CollectionOrdered $collectionOrdered;

    /**
     * @var array<string>
     */
    protected array $arrStrings;

    /**
     * @var int
     */
    protected int $badkey = -1;

    /**
     * @var string
     */
    protected string $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function setUp(): void
    {
        $this->collectionOrdered = new CollectionOrdered();
        $this->arrStrings = [];
    }

    /**
     * testLastIndexReturnsNegativeOneIfCollectionIsEmpty
     * @covers \pvc\struct\collection\CollectionOrdered::lastIndex
     */
    public function testLastIndexReturnsNegativeOneIfCollectionIsEmpty(): void
    {
        self::assertTrue($this->collectionOrdered->isEmpty());
        self::assertEquals(-1, $this->collectionOrdered->lastIndex());
    }

    /**
     * testLastIndexReturnsCorrectValueForNonEmptyCollection
     * @covers \pvc\struct\collection\CollectionOrdered::lastIndex
     */
    public function testLastIndexReturnsCorrectValueForNonEmptyCollection(): void
    {
        $this->addElements(3);
        self::assertEquals(2, $this->collectionOrdered->lastIndex());
    }

    protected function addElements(int $n, int $stringLength = 10): void
    {
        $this->addToCollection($n, $this->collectionOrdered, $stringLength);
    }

    /**
     * testDeleteInvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::delete
     */
    public function testDeleteInvalidKeyException(): void
    {
        $this->addElements(3);
        $this->expectException(InvalidKeyException::class);
        $this->collectionOrdered->delete($this->badkey);
    }

    /**
     * testDeleteNonExistentKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::delete
     */
    public function testDeleteNonExistentKeyException(): void
    {
        $this->expectException(NonExistentKeyException::class);
        $this->collectionOrdered->delete(2);
    }

    /**
     * testDeleteThenAddInMiddle
     * @covers \pvc\struct\collection\CollectionOrdered::delete
     * @covers \pvc\struct\collection\CollectionOrdered::add
     * @covers \pvc\struct\collection\CollectionOrdered::shuffleUp
     * @covers \pvc\struct\collection\CollectionOrdered::shuffleDown
     */
    public function testDeleteThenAddInMiddle(): void
    {
        $this->addElements(3);

        self::assertEquals(3, count($this->collectionOrdered));

        $this->collectionOrdered->delete(1);
        self::assertEquals(2, count($this->collectionOrdered));

        // note the automatic reindexing
        self::assertEquals($this->arrStrings[2], $this->collectionOrdered->getElement(1));

        $this->collectionOrdered->add(1, $this->arrStrings[1]);
        // note the automatic reindexing
        self::assertEquals($this->arrStrings[2], $this->collectionOrdered->getElement(2));
    }

    /**
     * testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection
     * this test also confirms that the shuffle method does nothing if the count of the collection is less than 2
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\CollectionOrdered::add
     */
    public function testAddPushesNewElementOnEndOfCollectionIfIndexIsGreaterThanCountOfCollection(): void
    {
        $newValue = 'foo';
        $proposedNewKeyIndex = 5;
        $expectedNewKeyIndex = 0;
        self::assertTrue($this->collectionOrdered->isEmpty());
        $this->collectionOrdered->add($proposedNewKeyIndex, $newValue);
        self::assertEquals($newValue, $this->collectionOrdered->getElement($expectedNewKeyIndex));
    }

    /**
     * testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     */
    public function testSetIndexThrowsExceptionIfKeyToElementToMoveDoesNotExist(): void
    {
        $this->addElements(6);
        self::expectException(NonExistentKeyException::class);
        $this->collectionOrdered->setIndex(10, 4);
    }

    /**
     * testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     * @covers \pvc\struct\collection\CollectionOrdered::getIndex
     */
    public function testSetIndexMovesElementToEndIfNewIndexIsGreaterThanOrEqualToLastIndex(): void
    {
        $this->addElements(6);

        $existingKey = 4;
        $newProposedKey = 10;
        $expectedNewKey = 5;
        $expectedNewIndex = 5;

        $element = $this->collectionOrdered->getElement($existingKey);
        $this->collectionOrdered->setIndex($existingKey, $newProposedKey);
        self::assertEquals($expectedNewKey, $this->collectionOrdered->getKey($element));
        self::assertEquals($expectedNewIndex, $this->collectionOrdered->getIndex($expectedNewKey));
    }

    /**
     * testSetIndexKeyLessThanNewIndex
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     */
    public function testSetIndexKeyLessThanNewIndex(): void
    {
        $this->addElements(6);

        $this->collectionOrdered->setIndex(1, 3);
        static::assertEquals($this->arrStrings[2], $this->collectionOrdered->getElement(1));
        static::assertEquals($this->arrStrings[3], $this->collectionOrdered->getElement(2));
        static::assertEquals($this->arrStrings[1], $this->collectionOrdered->getElement(3));
    }

    /**
     * testSetIndexNewIndexLessThanKey
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     */
    public function testSetIndexNewIndexLessThanKey(): void
    {
        $this->addElements(6);

        $this->collectionOrdered->setIndex(5, 3);
        static::assertEquals($this->arrStrings[3], $this->collectionOrdered->getElement(4));
        static::assertEquals($this->arrStrings[4], $this->collectionOrdered->getElement(5));
        static::assertEquals($this->arrStrings[5], $this->collectionOrdered->getElement(3));
    }

    /**
     * testSetIndexDoesNothingIfKeyAndNewIndexAreEqual
     * @covers \pvc\struct\collection\CollectionOrdered::setIndex
     */
    public function testSetIndexDoesNothingIfKeyAndNewIndexAreEqual(): void
    {
        $this->addElements(6);
        $newIndex = 2;
        $existingkey = 2;

        $this->collectionOrdered->setIndex($existingkey, $newIndex);
        self::assertEquals($newIndex, $this->collectionOrdered->getIndex($existingkey));
    }
}
