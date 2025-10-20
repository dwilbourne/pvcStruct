<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\InvalidValueException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvcTests\struct\unit_tests\collection\fixtures\Element;

/**
 * Class CollectionTest
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection<non-negative-int, Element>
     */
    protected Collection $collection;

    /**
     * @var array<non-negative-int, Element>
     */
    protected array $elements;

    public function setUp(): void
    {
        /** @var Collection<non-negative-int, Element> $collection */
        $collection = new Collection();
        $this->collection = $collection;
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\Collection::__construct
     * @covers \pvc\struct\collection\Collection::setInnerIterator
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(Collection::class, $this->collection);
    }

    /**
     * @param  non-negative-int  $n
     *
     * @return void
     */
    protected function addElements(int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            $this->elements[$i] = new Element();
        }
        $iterator = new ArrayIterator($this->elements);
        /** @var Collection<non-negative-int, Element> $collection */
        $collection = new Collection($iterator);
        $this->collection = $collection;
    }


    /**
     * testIsEmpty
     *
     * @covers \pvc\struct\collection\Collection::isEmpty
     */
    public function testIsEmpty(): void
    {
        self::assertTrue($this->collection->isEmpty());
        $this->addElements(3);
        self::assertFalse($this->collection->isEmpty());
    }


    /**
     * testIteration
     *
     * @coversNothing
     */
    public function testIteration(): void
    {
        $this->addElements(3);

        /**
         * @var non-negative-int $i
         */
        foreach ($this->collection as $i => $element) {
            self::assertEquals($i, $this->collection->key());
            self::assertEquals(
                $this->elements[$i++],
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
        self::assertEquals(0, $this->collection->count());

        $this->addElements(3);
        self::assertEquals(
            count($this->elements),
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
        $invalidKey = 100;
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($invalidKey)->willReturn(false);
        $this->collection->keyTester = $keyTester;
        self::expectException(InvalidKeyException::class);
        $this->collection->getElement($invalidKey);
    }

    /**
     * testGetElementThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::getElement
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testGetElementThrowsExceptionWithNonExistentKey(): void
    {
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
            $this->elements[0],
            $this->collection->getElement(0)
        );
    }

    /**
     * testGetElements
     *
     * @covers \pvc\struct\collection\Collection::getElements
     * @covers \pvc\struct\collection\Collection::initialize
     */
    public function testGetElements(): void
    {
        self::assertEmpty($this->collection->getElements());

        $this->addElements(4);

        /**
         * unsorted
         */
        $elements = $this->collection->getElements();
        self::assertEqualsCanonicalizing($this->elements, $elements);
    }

    /**
     * testGetKeyReturnsNullIfValueNotInList
     *
     * @covers \pvc\struct\collection\Collection::findElementKey
     */
    public function testFindElementKeyReturnsNullIfValueNotInList(): void
    {
        $valTester = $this->createMock(ValTesterInterface::class);
        $valTester->method('testValue')->willReturn(false);
        $this->addElements(3);
        self::assertNull($this->collection->findElementKey($valTester));
    }

    /**
     * testFindElementKeysReturnsEmptyArrayIfValueNotFound
     *
     * @covers \pvc\struct\collection\Collection::findElementKeys
     */
    public function testFindElementKeysReturnsEmptyArrayIfValueNotFound(): void
    {
        $this->addElements(3);
        $valTester = $this->createMock(ValTesterInterface::class);
        $valTester->method('testValue')->willReturn(false);
        $result = $this->collection->findElementKeys($valTester);
        self::assertEmpty($result);
    }

    /**
     * testGetIndexThrowsExceptionWithInvalidKey
     * @return void
     * @covers \pvc\struct\collection\Collection::getIndex
     */
    public function testGetIndexThrowsExceptionWithInvalidKey(): void
    {
        $invalidKey = 1;
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($invalidKey)->willReturn(false);
        $this->collection->keyTester = $keyTester;
        self::expectException(InvalidKeyException::class);
        $this->collection->getIndex($invalidKey);
    }

    /**
     * testGetIndexThrowsExceptionWithNonExistentKey
     * @return void
     * @covers \pvc\struct\collection\Collection::getIndex
     */
    public function testGetIndexThrowsExceptionWithNonExistentKey(): void
    {
        $invalidKey = 2;
        self::expectException(NonExistentKeyException::class);
        $this->collection->getIndex($invalidKey);
    }

    /**
     * testAddThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::validateNewKey
     */
    public function testAddThrowsExceptionWithInvalidKey(): void
    {
        $invalidKey = 5;
        $element = new Element();
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($invalidKey)->willReturn(false);
        $this->collection->keyTester = $keyTester;

        $this->expectException(InvalidKeyException::class);
        $this->collection->add($element, $invalidKey);
    }

    /**
     * testAddThrowsExceptionsWithDuplicateKey
     *
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::validateNewKey
     */
    public function testAddThrowsExceptionsWithDuplicateKey(): void
    {
        $key = 5;
        $element = new Element();
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($key)->willReturn(true);
        $this->collection->keyTester = $keyTester;

        $this->collection->add($element, $key);
        $this->expectException(DuplicateKeyException::class);
        $this->collection->add($element, $key);
    }

    /**
     * testAddThrowsExceptionWithInvalidValue
     * @return void
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::validateValue
     */
    public function testAddThrowsExceptionWithInvalidValue(): void
    {
        $key = 5;
        $element = new Element();
        $valueTester = $this->createMock(ValTesterInterface::class);
        $valueTester->method('testValue')->with($element)->willReturn(false);
        $this->collection->valueTester = $valueTester;
        $this->expectException(InvalidValueException::class);
        $this->collection->add($element, $key);
    }

    /**
     * testAdd
     *
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::getIndex
     */
    public function testAdd(): void
    {
        /**
         * adds 3 elements to the collection and sets $this->elements to
         * a list of the 3 elements
         */
        $this->addElements(3);
        /**
         * @var Element $value
         * @var non-negative-int $key
         */
        foreach ($this->elements as $key => $value) {
            self::assertEquals($value, $this->collection->getElement($key));
        }
        /**
         * indices are zero-based
         */
        self::assertEquals(2, $this->collection->getIndex(2));
    }

    /**
     * testUpdateThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::update
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testUpdateThrowsExceptionWithInvalidKey(): void
    {
        $invalidKey = 5;
        $element = new Element();
        $this->collection->add($element, $invalidKey);

        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($invalidKey)->willReturn(false);
        $this->collection->keyTester = $keyTester;

        self::expectException(InvalidKeyException::class);
        $this->collection->update($invalidKey, $element);
    }

    /**
     * testUpdateThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::update
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testUpdateThrowsExceptionWithNonExistentKey(): void
    {
        $nonExistentKey = 5;
        $element = new Element();
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($nonExistentKey)->willReturn(true);
        $this->collection->keyTester = $keyTester;
        self::expectException(NonExistentKeyException::class);
        $this->collection->update($nonExistentKey, $element);
    }

    /**
     * testUpdateThrowsExceptionWithInvalidValue
     * @return void
     * @covers \pvc\struct\collection\Collection::update
     * @covers \pvc\struct\collection\Collection::validateValue
     */
    public function testUpdateThrowsExceptionWithInvalidValue(): void
    {
        $key = 5;
        $element = new Element();
        $this->collection->add($element, $key);

        $updatedElement = new Element();
        $valueTester = $this->createMock(ValTesterInterface::class);
        $valueTester->method('testValue')->with($updatedElement)->willReturn(false);
        $this->collection->valueTester = $valueTester;

        self::expectException(InvalidValueException::class);
        $this->collection->update($key, $updatedElement);
    }

    /**
     * testUpdate
     *
     * @covers \pvc\struct\collection\Collection::update
     * @covers \pvc\struct\collection\Collection::validateValue
     */
    public function testUpdate(): void
    {
        $this->addElements(3);
        $newElement = new Element();
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
     * @covers \pvc\struct\collection\Collection::validateExistingKey
     */
    public function testDeleteThrowsExceptionWithInvalidKey(): void
    {
        $badKey = 2;
        $keyTester = $this->createMock(ValTesterInterface::class);
        $keyTester->method('testValue')->with($badKey)->willReturn(false);
        $this->collection->keyTester = $keyTester;
        $this->expectException(InvalidKeyException::class);
        $this->collection->delete($badKey);
    }

    /**
     * testDeleteThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::delete
     */
    public function testDeleteThrowsExceptionWithNonExistentKey(): void
    {
        $nonexistentKey = 2;
        $this->expectException(NonExistentKeyException::class);
        $this->collection->delete($nonexistentKey);
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
        $this->addElements(3);
        self::assertSame($this->elements[0], $this->collection->getFirst());
        self::assertSame($this->elements[2], $this->collection->getLast());
        self::assertSame($this->elements[1], $this->collection->getNth(1));
        self::assertSame($this->elements[2], $this->collection->getNth(2));
        self::assertNull($this->collection->getNth(4));
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\Collection::setComparator
     * @covers \pvc\struct\collection\Collection::getElements
     * @covers \pvc\struct\collection\Collection::add
     * @covers \pvc\struct\collection\Collection::update
     *
     */
    public function testOrderingBehavior(): void
    {
        /**
         * although the technically correct way to do this is to mock the
         * comparator property of the collection, creating a real comparator
         * is the same code as creating the mock with a return callback.
         */
        $this->addElements(2);
        $elementA = $this->elements[0];
        $elementB = $this->elements[1];

        /**
         * default behavior is the order in which the elements are added to the collection
         */
        $expectedResult = [0 => $elementA, 1 => $elementB];
        self::assertEquals($expectedResult, $this->collection->getElements());

        /**
         * collection reorders when comparator is set and element values are
         * established
         */
        $elementA->setValue('c');
        $elementB->setValue('b');
        $comparator = function (Element $a, Element $b) {
            return $a->getValue() <=> $b->getValue();
        };
        $this->collection->setComparator($comparator);

        /**
         * elements are now in alphabetical order
         */
        $expectedResult = [1 => $elementB, 0 => $elementA];
        self::assertEquals($expectedResult, $this->collection->getElements());

        /**
         * new elements are added and the collection remains sorted correctly
         */
        $elementC = new Element();
        $elementC->setValue('a');
        $this->collection->add($elementC, 2);
        $expectedResult = [2 => $elementC, 1 => $elementB, 0 => $elementA];
        self::assertEquals($expectedResult, $this->collection->getElements());

        /**
         * update an element and the sort order is maintained
         */
        $elementD = new Element();
        $elementD->setValue('g');
        $this->collection->update(2, $elementD);
        $expectedResult = [1 => $elementB, 0 => $elementA, 2 => $elementD];
        self::assertEquals($expectedResult, $this->collection->getElements());
    }
}
