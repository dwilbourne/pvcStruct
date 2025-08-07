<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;
use pvcTests\struct\unit_tests\collection\fixtures\Element;
use pvcTests\struct\unit_tests\collection\fixtures\ElementFactory;

/**
 * Class CollectionTest
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection<Element>
     */
    protected Collection $collection;

    /**
     * @var array<Element>
     */
    protected array $elements;

    protected ElementFactory $elementFactory;

    public function setUp(): void
    {
        $this->elementFactory = new ElementFactory();
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\Collection::__construct
     * @covers \pvc\struct\collection\Collection::setInnerIterator
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
        $this->elements
            = $this->elementFactory->makeElementArray($n);
        $this->collection = new Collection($this->elements);
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
        $this->addElements(2);
        self::expectException(NonExistentKeyException::class);
        $this->collection->getElement(-2);
    }

    /**
     * testGetElementThrowsExceptionWithNonExistentKey
     *
     * @covers \pvc\struct\collection\Collection::getElement
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
    public function testGetElementsAndThenInitialize(): void
    {
        $this->collection = new Collection();
        self::assertEmpty($this->collection->getElements());

        $this->addElements(4);

        /**
         * unsorted
         */
        $elements = $this->collection->getElements();
        self::assertIsArray($elements);
        self::assertEqualsCanonicalizing($this->elements, $elements);

        $this->collection->initialize();
        self::assertTrue($this->collection->isEmpty());
    }

    /**
     * testGetKeyReturnsNullIfValueNotInList
     *
     * @covers \pvc\struct\collection\Collection::findElementKey
     */
    public function testFindElementKeyReturnsNullIfValueNotInList(): void
    {
        $this->addElements(3);
        $valTester = $this->createMock(ValTesterInterface::class);
        $valTester->method('testValue')->willReturn(false);
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
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    /**
     * testAddThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::add
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
     * @covers \pvc\struct\collection\Collection::getIndex
     */
    public function testAdd(): void
    {
        $this->collection = new Collection();
        $this->elements
            = $this->elementFactory->makeElementArray(3);
        foreach ($this->elements as $key => $value) {
            $this->collection->add($key, $value);
            self::assertEquals($value, $this->collection->getElement($key));
        }
        /**
         * indices are zero-based
         */
        self::assertEquals(2, $this->collection->getIndex(2));
        self::assertNull($this->collection->getIndex(5));
    }

    /**
     * testUpdateThrowsExceptionWithInvalidKey
     *
     * @covers \pvc\struct\collection\Collection::update
     */
    public function testUpdateThrowsExceptionWithInvalidKey(): void
    {
        $this->collection = new Collection();
        $this->expectException(NonExistentKeyException::class);
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
     */
    public function testDeleteThrowsExceptionWithInvalidKey(): void
    {
        $this->collection = new Collection();
        $this->expectException(NonExistentKeyException::class);
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
        $a = $this->elementFactory->makeElement($key, $indexed);

        $key = 10;
        $b = $this->elementFactory->makeElement($key, $indexed);

        $key = 10;
        $c = $this->elementFactory->makeElement($key, $indexed);

        $this->collection = new Collection([$a, $b, $c]);

        self::assertSame($a, $this->collection->getFirst());
        self::assertSame($c, $this->collection->getLast());
        self::assertSame($b, $this->collection->getNth(1));
        self::assertSame($c, $this->collection->getNth(2));
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
         * value is 'd'
         */
        $elementA = $this->elementFactory->makeElement(3);

        /**
         * value is 'c'
         */
        $elementB = $this->elementFactory->makeElement( 2);

        /**
         * default behavior is the order in which the elements are added to the collection
         */
        $expectedResult = [$elementA, $elementB];
        $collection = new Collection([$elementA, $elementB]);
        self::assertEquals($expectedResult, $collection->getElements());

        /**
         * collection reorders when comparator is set
         */
        $comparator = function (Element $a, Element $b) {
            return $a->getValue() <=> $b->getValue();
        };
        $collection->setComparator($comparator);

        /**
         * elements are now in alphabetical order
         */
        $expectedResult = [1 => $elementB, 0 => $elementA];
        self::assertEquals($expectedResult, $collection->getElements());

        /**
         * new elements are added and the collection remains sorted correctly
         */
        $elementC = $this->elementFactory->makeElement(1);
        $collection->add(2, $elementC);
        $expectedResult = [2 => $elementC, 1 => $elementB, 0 => $elementA];
        self::assertEquals($expectedResult, $collection->getElements());

        /**
         * update an element and the sort order is maintained
         * value = 'g'
         */
        $elementD = $this->elementFactory->makeElement(6);
        $collection->update(2, $elementD);
        $expectedResult = [1 => $elementB, 0 => $elementA, 2 => $elementD];
        self::assertEquals($expectedResult, $collection->getElements());
    }
}
