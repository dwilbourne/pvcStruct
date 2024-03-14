<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionAbstract;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class CollectionAbstractTest
 */
class CollectionAbstractTest extends TestCase
{
    use CollectionTestingTrait;

    /**
     * @var CollectionAbstract<string>|MockObject
     */
    protected CollectionAbstract $collectionAbstract;

    /**
     * @var array<string>
     */
    protected array $arrStrings;

    /**
     * @var int
     */
    protected int $badkey = -1;

    public function setUp(): void
    {
        $this->collectionAbstract = $this->getMockForAbstractClass(CollectionAbstract::class);
        $this->arrStrings = [];
    }

    /**
     * testIsEmpty
     * @covers \pvc\struct\collection\CollectionAbstract::isEmpty
     */
    public function testIsEmpty(): void
    {
        self::assertTrue($this->collectionAbstract->isEmpty());
        $this->addElements(3);
        self::assertFalse($this->collectionAbstract->isEmpty());
    }

    protected function addElements(int $n, int $stringLength = 10): void
    {
        $this->addToCollection($n, $this->collectionAbstract, $stringLength);
    }

    /**
     * testIteration
     * @covers \pvc\struct\collection\CollectionAbstract::current
     * @covers \pvc\struct\collection\CollectionAbstract::key
     * @covers \pvc\struct\collection\CollectionAbstract::valid
     * @covers \pvc\struct\collection\CollectionAbstract::next
     * @covers \pvc\struct\collection\CollectionAbstract::rewind
     */
    public function testIteration(): void
    {
        $this->addElements(4);
        $i = 0;
        foreach ($this->collectionAbstract as $element) {
            self::assertEquals($i, $this->collectionAbstract->key());
            self::assertEquals($this->arrStrings[$i++], $this->collectionAbstract->current());
            self::assertTrue($this->collectionAbstract->valid());
        }
        self::assertFalse($this->collectionAbstract->valid());
        $this->collectionAbstract->rewind();
        self::assertEquals(0, $this->collectionAbstract->key());
    }

    /**
     * testCount
     * @covers \pvc\struct\collection\CollectionAbstract::count
     */
    public function testCount(): void
    {
        $countOfElements = 7;
        $this->addElements($countOfElements);
        self::assertEquals($countOfElements, count($this->collectionAbstract));
    }

    /**
     * testGetElementThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\collection\CollectionAbstract::getElement
     * @covers \pvc\struct\collection\CollectionAbstract::validateExistingKey
     */
    public function testGetElementThrowsExceptionWithInvalidKey(): void
    {
        self::expectException(InvalidKeyException::class);
        $element = $this->collectionAbstract->getElement(-2);
    }

    /**
     * testGetElementThrowsExceptionWithNonExistentKey
     * @covers \pvc\struct\collection\CollectionAbstract::getElement
     * @covers \pvc\struct\collection\CollectionAbstract::validateKey
     * @covers \pvc\struct\collection\CollectionAbstract::validateExistingKey
     */
    public function testGetElementThrowsExceptionWithNonExistentKey(): void
    {
        self::expectException(NonExistentKeyException::class);
        $element = $this->collectionAbstract->getElement(2);
    }

    /**
     * testGetElement
     * @covers \pvc\struct\collection\CollectionAbstract::getElement
     */
    public function testGetElementReturnsCorrectValue(): void
    {
        $this->addElements(1);
        self::assertEquals($this->arrStrings[0], $this->collectionAbstract->getElement(0));
    }

    /**
     * testGetElements
     * @covers \pvc\struct\collection\CollectionAbstract::getElements
     */
    public function testGetElements(): void
    {
        self::assertIsArray($this->collectionAbstract->getElements());
        self::assertEmpty($this->collectionAbstract->getElements());
        $this->addElements(3);
        $elements = $this->collectionAbstract->getElements();
        self::assertIsArray($elements);
        self::assertEqualsCanonicalizing($this->arrStrings, $elements);
    }

    /**
     * testGetKeyReturnsNullIfValueNotInList
     * @covers \pvc\struct\collection\CollectionAbstract::getKey
     */
    public function testGetKeyReturnsNullIfValueNotInList(): void
    {
        /**
         * insert 5 elements with a string length of 8, try to find an element that happens to have a string
         * length of 3.
         */
        $this->addElements(5, 8);
        $needle = 'foo';
        self::assertNull($this->collectionAbstract->getKey($needle));
    }

    /**
     * testGetKeyReturnsKeyForFirstOccurrenceOfString
     * @covers \pvc\struct\collection\CollectionAbstract::getKey
     */
    public function testGetKeyReturnsKeyForFirstOccurrenceOfString(): void
    {
        $this->addElements(5, 8);
        $needle = $this->arrStrings[1];
        $this->collectionAbstract->push($needle);
        /**
         * $needle now exists at 0-based positions 1 and 5
         */
        self::assertEquals(1, $this->collectionAbstract->getKey($needle));
    }

    /**
     * testGetKeysReturnsEmptyArrayIfValueNotFound
     * @covers \pvc\struct\collection\CollectionAbstract::getKeys
     */
    public function testGetKeysReturnsEmptyArrayIfValueNotFound(): void
    {
        /**
         * insert 5 elements with a string length of 8, try to find an element that happens to have a string
         * length of 4.
         */
        $this->addElements(5, 8);
        $needle = 'foo';
        $result = $this->collectionAbstract->getKeys($needle);
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    /**
     * testGetKeysReturnsIndicesOfElementThatAppearsMultipleTimesInCollection
     * @covers \pvc\struct\collection\CollectionAbstract::getKeys
     */
    public function testGetKeysReturnsIndicesOfElementThatAppearsMultipleTimesInCollection(): void
    {
        $this->addElements(5, 8);
        $needle = $this->arrStrings[1];
        $this->collectionAbstract->push($needle);
        /**
         * $needle now exists at 0-based positions 1 and 5
         */
        self::assertEqualsCanonicalizing([1, 5], $this->collectionAbstract->getKeys($needle));
    }

    /**
     * testAddThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\collection\CollectionAbstract::add
     * @covers \pvc\struct\collection\CollectionAbstract::validateKey
     * @covers \pvc\struct\collection\CollectionAbstract::validateNewKey
     */
    public function testAddThrowsExceptionWithInvalidKey(): void
    {
        $this->addElements(3);
        $this->expectException(InvalidKeyException::class);
        $this->collectionAbstract->add($this->badkey, 'some payload');
    }

    /**
     * testAddThrowsExceptionsWithDuplicateKey
     * @covers \pvc\struct\collection\CollectionAbstract::add
     * @covers \pvc\struct\collection\CollectionAbstract::validateNewKey
     */
    public function testAddThrowsExceptionsWithDuplicateKey(): void
    {
        $this->addElements(3);
        $this->expectException(DuplicateKeyException::class);
        $this->collectionAbstract->add(0, 'cannot add because key already exists');
    }

    /**
     * testAdd
     * @covers \pvc\struct\collection\CollectionAbstract::add
     */
    public function testAdd(): void
    {
        $testKey1 = 0;
        $testValue1 = 'foo';
        $testKey2 = 4;
        $testValue2 = 'bar';

        $this->collectionAbstract->add($testKey1, $testValue1);
        self::assertEquals(1, $this->collectionAbstract->count());
        self::assertEquals($testValue1, $this->collectionAbstract->getElement($testKey1));

        $this->collectionAbstract->add($testKey2, $testValue2);
        self::assertEquals(2, $this->collectionAbstract->count());
        self::assertEquals($testValue2, $this->collectionAbstract->getElement($testKey2));
    }

    /**
     * testUpdateThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\collection\CollectionAbstract::update
     */
    public function testUpdateThrowsExceptionWithInvalidKey(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->collectionAbstract->update($this->badkey, 'some payload');
    }

    /**
     * testUpdateThrowsExceptionWithNonExistentKey
     * @covers \pvc\struct\collection\CollectionAbstract::update
     */
    public function testUpdateThrowsExceptionWithNonExistentKey(): void
    {
        $this->expectException(NonExistentKeyException::class);
        $this->collectionAbstract->update(4, 'some payload');
    }

    /**
     * testUpdate
     * @covers \pvc\struct\collection\CollectionAbstract::update
     */
    public function testUpdate(): void
    {
        $this->addElements(3);

        $value_other = 'some other thing';
        $this->collectionAbstract->update(1, $value_other);

        $value_1 = $this->collectionAbstract->getElement(1);
        static::assertSame($value_other, $value_1);
    }

    /**
     * testDeleteThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\collection\CollectionAbstract::delete
     */
    public function testDeleteThrowsExceptionWithInvalidKey(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->collectionAbstract->delete($this->badkey, 'some payload');
    }

    /**
     * testDeleteThrowsExceptionWithNonExistentKey
     * @covers \pvc\struct\collection\CollectionAbstract::delete
     */
    public function testDeleteThrowsExceptionWithNonExistentKey(): void
    {
        $this->expectException(NonExistentKeyException::class);
        $this->collectionAbstract->delete(4);
    }

    /**
     * testDelete
     * @covers \pvc\struct\collection\CollectionAbstract::delete
     */
    public function testDelete(): void
    {
        $this->addElements(3);

        $this->collectionAbstract->delete(1);
        self::assertEquals(2, count($this->collectionAbstract));
        // keys are not reindexed - there is no key with payload == 1 now
        self::expectException(NonExistentKeyException::class);
        $foo = $this->collectionAbstract->getElement(1);
    }

    /**
     * testPushWithEmptyCollection
     * @covers \pvc\struct\collection\CollectionAbstract::push
     */
    public function testPushWithEmptyCollection(): void
    {
        $element = $this->randomString(8);
        $this->collectionAbstract->push($element);
        self::assertEquals($element, $this->collectionAbstract->getElement(0));
    }

    /**
     * testPushWithPopulatedCollection
     * @covers \pvc\struct\collection\CollectionAbstract::push
     */
    public function testPushWithPopulatedCollection(): void
    {
        $numberOfElementsToAdd = 3;
        $elementToPush = $this->randomString(8);
        $this->addElements($numberOfElementsToAdd);
        $this->collectionAbstract->push($elementToPush);
        self::assertEquals($elementToPush, $this->collectionAbstract->getElement($numberOfElementsToAdd));
    }
}
