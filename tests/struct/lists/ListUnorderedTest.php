<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists;

use pvc\struct\lists\err\DuplicateKeyException;
use pvc\struct\lists\err\InvalidKeyException;
use pvc\struct\lists\err\NonExistentKeyException;

use PHPUnit\Framework\TestCase;
use pvc\struct\lists\ListUnordered;
use stdClass;

class ListUnorderedTest extends TestCase
{
    protected ListUnordered $ulist;
    protected array $arrStrings;
    protected int $badkey;
    protected string $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function setUp(): void
    {
        $this->badkey = -1;
        $this->ulist = new ListUnordered();
        $this->arrStrings = [];
    }

    protected function addElements(int $n, int $stringLength = 10): void
    {
        for ($i = 0; $i < $n; $i++) {
            $this->arrStrings[$i] = $this->randomString($stringLength);
            $this->ulist->add($i, $this->arrStrings[$i]);
        }
    }

    protected function randomString(int $length = 64): string
    {
        $pieces = [];
        $max = mb_strlen($this->keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $this->keySpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }


    /**
     * testIsEmpty
     * @covers \pvc\struct\lists\ListUnordered::isEmpty
     */
    public function testIsEmpty(): void
    {
        self::assertTrue($this->ulist->isEmpty());
        $this->addElements(3);
        self::assertFalse($this->ulist->isEmpty());
    }

    /**
     * testGetKeys
     * @covers \pvc\struct\lists\ListUnordered::getKeys
     */
    public function testGetKeys(): void
    {
        self::assertIsArray($this->ulist->getKeys());
        self::assertEmpty($this->ulist->getKeys());
        $this->addElements(3);
        // 0-indexed
        $expectedResult = [0, 1, 2];
        self::assertEqualsCanonicalizing($expectedResult, $this->ulist->getKeys());
    }

    /**
     * testGetElement
     * @covers \pvc\struct\lists\ListUnordered::getElement
     */
    public function testGetElement(): void
    {
        $this->addElements(1);
        self::assertIsString($this->ulist->getElement(0));
    }

    /**
     * testGetElementException
     * @throws NonExistentKeyException
     * @covers \pvc\struct\lists\ListUnordered::getElement
     */
    public function testGetElementException(): void
    {
        self::expectException(NonExistentKeyException::class);
        $foo = $this->ulist->getElement($this->badkey);
    }

	/**
	 * testGetElementsReturnsEmptyArray
	 * @covers \pvc\struct\lists\ListUnordered::getElements
	 */
	public function testGetElementsReturnsEmptyArrayFromEmptyList() : void
	{
		self::assertIsArray($this->ulist->getElements());
	}

	/**
	 * testGetElementsReturnsAllElements
	 * @covers \pvc\struct\lists\ListUnordered::getElements
	 */
	public function testGetElementsReturnsAllElements() : void
	{
		$numberOfElements = 5;
		$this->addElements($numberOfElements);
		/**
		 * move the internal pointer
		 */
		$this->ulist->next();
		$this->ulist->next();
		self::assertEquals($numberOfElements, count($this->ulist->getElements()));
	}

    /**
     * testAddThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\lists\ListUnordered::add
     * @covers \pvc\struct\lists\ListUnordered::validateKey
     */
    public function testAddThrowsExceptionWithInvalidKey(): void
    {
        $this->addElements(3);
        $this->expectException(InvalidKeyException::class);
        $this->ulist->add($this->badkey, 'some value');
    }

    /**
     * testAddThrowsExceptionsWithDuplicateKey
     * @covers \pvc\struct\lists\ListUnordered::add
     */
    public function testAddThrowsExceptionsWithDuplicateKey(): void
    {
        $this->addElements(3);
        $this->expectException(DuplicateKeyException::class);
        $this->ulist->add(0, 'cannot add because key already exists');
    }

    /**
     * testAdd
     * @covers \pvc\struct\lists\ListUnordered::add
     */
    public function testAdd(): void
    {
        $this->addElements(3);
        self::assertEquals(3, $this->ulist->count());
    }

    /**
     * testUpdateThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\lists\ListUnordered::update
     */
    public function testUpdateThrowsExceptionWithInvalidKey(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->ulist->update($this->badkey, 'some value');
    }

    /**
     * testUpdateThrowsExceptionWithNonExistentKey
     * @covers \pvc\struct\lists\ListUnordered::update
     */
    public function testUpdateThrowsExceptionWithNonExistentKey(): void
    {
        $this->expectException(NonExistentKeyException::class);
        $this->ulist->update(4, 'some value');
    }

    /**
     * testUpdate
     * @covers \pvc\struct\lists\ListUnordered::update
     */
    public function testUpdate(): void
    {
        $this->addElements(3);

        $value_other = 'some other thing';
        $this->ulist->update(1, $value_other);

        $value_1 = $this->ulist->getElement(1);
        static::assertSame($value_other, $value_1);
    }

    /**
     * testDeleteThrowsExceptionWithInvalidKey
     * @covers \pvc\struct\lists\ListUnordered::delete
     */
    public function testDeleteThrowsExceptionWithInvalidKey(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->ulist->delete($this->badkey, 'some value');
    }

    /**
     * testDeleteThrowsExceptionWithNonExistentKey
     * @covers \pvc\struct\lists\ListUnordered::delete
     */
    public function testDeleteThrowsExceptionWithNonExistentKey(): void
    {
        $this->expectException(NonExistentKeyException::class);
        $this->ulist->delete(4);
    }

    /**
     * testDelete
     * @covers \pvc\struct\lists\ListUnordered::delete
     */
    public function testDelete(): void
    {
        $this->addElements(3);

        $this->ulist->delete(1);
        self::assertEquals(2, count($this->ulist));
        // keys are not reindexed - there is no key with value == 1 now
        self::expectException(NonExistentKeyException::class);
        $foo = $this->ulist->getElement(1);
    }

    /**
     * testPushEmptyList
     * @covers \pvc\struct\lists\ListUnordered::push
     */
    public function testPushEmptyList(): void
    {
        $this->ulist->push('some value');
        static::assertEquals(1, count($this->ulist));
        static::assertTrue($this->ulist->offsetExists(0));
    }

    /**
     * testPushPopulatedList
     * @covers \pvc\struct\lists\ListUnordered::push
     */
    public function testPushPopulatedList(): void
    {
        $this->addElements(3);
        $this->ulist->push('some value');
        static::assertEquals(4, count($this->ulist));
        static::assertTrue($this->ulist->offsetExists(3));
    }
}
