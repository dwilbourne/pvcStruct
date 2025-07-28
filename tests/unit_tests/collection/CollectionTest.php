<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvcTests\struct\unit_tests\collection\fixtures\CollectionElement;
use pvcTests\struct\unit_tests\collection\fixtures\CollectionElementFactory;

/**
 * Class CollectionTest
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection<CollectionElement>
     */
    protected Collection $collection;

    protected array $collectionElements;

    protected CollectionElementFactory $collectionElementFactory;

    public function setUp(): void
    {
        $this->collectionElementFactory = new CollectionElementFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\Collection::__construct
     */
    public function testConstruct(): void
    {
        $this->collection = new Collection();
        self::assertInstanceOf(Collection::class, $this->collection);
    }

    /**
     * testIsEmpty
     *
     * @covers \pvc\struct\collection\Collection::isEmpty
     */
    public function testIsEmpty(): void
    {
        $this->collection = new Collection();
        self::assertTrue($this->collection->isEmpty());
        $this->addElements(3);
        self::assertFalse($this->collection->isEmpty());
    }

    /**
     * @param  non-negative-int  $n
     *
     * @return void
     */
    protected function addElements(int $n): void
    {
        $this->collectionElements
            = $this->collectionElementFactory->makeCollectionElementArray($n);
        $this->collection = new Collection($this->collectionElements);
    }

    /**
     * testIteration
     *
     * @coversNothing
     */
    public function testIteration(): void
    {
        $this->addElements(3);

        foreach ($this->collection as $i => $element) {
            self::assertEquals($i, $this->collection->key());
            self::assertEquals(
                $this->collectionElements[$i++],
                $this->collection->current()
            );
            self::assertTrue($this->collection->valid());
        }
        self::assertFalse($this->collection->valid());
        $this->collection->rewind();
        self::assertEquals(0, $this->collection->key());
    }

    /**
     * testCount
     *
     * @covers \pvc\struct\collection\Collection::count
     */
    public function testCount(): void
    {
        $this->addElements(3);

        self::assertEquals(
            count($this->collectionElements),
            count($this->collection)
        );
    }

    /**
     * testGetElementThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::getElement
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testGetElementThrowsExceptionWithInvalidKey(): void
    {
        $this->addElements(2);
        self::expectException(InvalidKeyException::class);
        $element = $this->collection->getElement(-2);
    }

    /**
     * testGetElementThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::getElement
     * @covers \pvc\struct\collection\Collection::validateKey
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testGetElementThrowsExceptionWithNonExistentKey(): void
    {
        $this->addElements(3);
        self::expectException(NonExistentKeyException::class);
        $element = $this->collection->getElement(5);
        unset($element);
    }

    /**
     * testGetElement
     *
     * @covers \pvc\struct\collection\Collection::getElement
     */
    public function testGetElementReturnsCorrectValue(): void
    {
        $this->addElements(3);

        self::assertEquals(
            $this->collectionElements[0],
            $this->collection->getElement(0)
        );
    }

    /**
     * testGetElements
     *
     * @covers \pvc\struct\collection\Collection::getElements
     */
    public function testGetElements(): void
    {
        /**
         * sorted
         */
        $comparator = function (CollectionElement $a, CollectionElement $b) {
            return $a->getValue() <=> $b->getValue();
        };

        $this->collection = new Collection([], $comparator);
        self::assertEmpty($this->collection->getElements());

        $this->addElements(4);

        /**
         * unsorted
         */
        $elements = $this->collection->getElements();
        self::assertIsArray($elements);
        self::assertEqualsCanonicalizing($this->collectionElements, $elements);

        usort($elements, $comparator);
        self::assertEquals($elements, $this->collection->getElements());
    }

    /**
     * testGetKeyReturnsNullIfValueNotInList
     *
     * @covers \pvc\struct\collection\Collection::getKey
     */
    public function testGetKeyReturnsFalseIfValueNotInList(): void
    {
        $this->addElements(3);
        $needle = new CollectionElement();
        self::assertFalse($this->collection->getKey($needle));
    }

    /**
     * testGetKeysReturnsEmptyArrayIfValueNotFound
     *
     * @covers \pvc\struct\collection\Collection::getKeys
     */
    public function testGetKeysReturnsEmptyArrayIfValueNotFound(): void
    {
        $this->addElements(3);

        $needle = 'foo';
        $result = $this->collection->getKeys($needle);
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    /**
     * testAddThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::validateKey
     * @covers \pvc\struct\collection\Collection::validateNewKey
     */
    public function testAddThrowsExceptionWithInvalidKey(): void
    {
        $this->addElements(3);

        $badKey = -1;
        $this->expectException(InvalidKeyException::class);
        $this->collection->add($badKey, 'some payload');
    }

    /**
     * testAddThrowsExceptionsWithDuplicateKey
     *
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::validateNewKey
     */
    public function testAddThrowsExceptionsWithDuplicateKey(): void
    {
        $this->addElements(3);

        $this->expectException(DuplicateKeyException::class);
        $this->collection->add(0, 'cannot add because key already exists');
    }

    /**
     * testAdd
     *
     * @covers \pvc\struct\collection\Collection::add
     */
    public function testAdd(): void
    {
        $this->collection = new Collection();
        $this->collectionElements
            = $this->collectionElementFactory->makeCollectionElementArray(3);
        foreach ($this->collectionElements as $key => $value) {
            $this->collection->add($key, $value);
            self::assertEquals($value, $this->collection->getElement($key));
        }
    }

    /**
     * testUpdateThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::update
     */
    public function testUpdateThrowsExceptionWithInvalidKey(): void
    {
        $this->collection = new Collection();
        $this->expectException(InvalidKeyException::class);
        $badKey = -1;
        $this->collection->update($badKey, 'some payload');
    }

    /**
     * testUpdateThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::update
     */
    public function testUpdateThrowsExceptionWithNonExistentKey(): void
    {
        $this->addElements(2);
        $this->expectException(NonExistentKeyException::class);
        $this->collection->update(4, 'some payload');
    }

    /**
     * testUpdate
     *
     * @covers \pvc\struct\collection\Collection::update
     */
    public function testUpdate(): void
    {
        $this->addElements(3);
        $newElement = new CollectionElement();
        $testKey = 1;
        $this->collection->update($testKey, $newElement);

        static::assertSame(
            $newElement,
            $this->collection->getElement($testKey)
        );
    }

    /**
     * testDeleteThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::delete
     */
    public function testDeleteThrowsExceptionWithInvalidKey(): void
    {
        $this->collection = new Collection();
        $this->expectException(InvalidKeyException::class);
        $badKey = -1;
        $this->collection->delete($badKey, 'some payload');
    }

    /**
     * testDeleteThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::delete
     */
    public function testDeleteThrowsExceptionWithNonExistentKey(): void
    {
        $this->addElements(2);
        $this->expectException(NonExistentKeyException::class);
        $this->collection->delete(4);
    }

    /**
     * testDelete
     *
     * @covers \pvc\struct\collection\Collection::delete
     */
    public function testDelete(): void
    {
        $this->addElements(3);

        $this->collection->delete(1);
        self::assertEquals(2, count($this->collection));
        /**
         * no element with key == 1 now
         */
        self::expectException(NonExistentKeyException::class);
        $foo = $this->collection->getElement(1);
        unset($foo);
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\Collection::getFirst
     * @covers \pvc\struct\collection\Collection::getLast
     * @covers \pvc\struct\collection\Collection::getNth
     */
    public function testGetFirstLastNthElement(): void
    {
        $indexed = false;

        $key = 7;
        $a = $this->collectionElementFactory->makeElement($key, $indexed);

        $key = 10;
        $b = $this->collectionElementFactory->makeElement($key, $indexed);

        $key = 10;
        $c = $this->collectionElementFactory->makeElement($key, $indexed);

        $this->collection = new Collection([$a, $b, $c]);

        self::assertSame($a, $this->collection->getFirst());
        self::assertSame($c, $this->collection->getLast());
        self::assertSame($b, $this->collection->getNth(1));
        self::assertSame($c, $this->collection->getNth(2));
        self::assertSame($c, $this->collection->getNth(4));
    }
}
