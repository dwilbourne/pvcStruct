<?php

namespace pvcTests\struct\lists;

use PHPUnit\Framework\TestCase;
use pvc\struct\lists\err\NonExistentKeyException;
use pvc\struct\lists\ListOrdered;

class ListOrderedTest extends TestCase
{
    protected ListOrdered $olist;
    protected array $arrStrings;
    protected int $badkey;
    protected string $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function setUp(): void
    {
        $this->olist = new ListOrdered();
        $this->arrStrings = [];
        $this->badkey = -1;
    }

    protected function addElements(int $n, int $stringLength = 10): void
    {
        for ($i = 0; $i < $n; $i++) {
            $this->arrStrings[$i] = $this->randomString($stringLength);
            $this->olist->push($this->arrStrings[$i]);
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
     * testGetElement
     * @covers \pvc\struct\lists\ListOrdered::getElement
     */
    public function testGetElement(): void
    {
        $this->addElements(1);
        self::assertIsString($this->olist->getElement(0));
    }

    /**
     * testGetElementOnEmptyList
     * @covers \pvc\struct\lists\ListOrdered::getElement
     */
    public function testGetElementOnEmptyList() : void
    {
        $this->expectException(\OutOfRangeException::class);
        $foo = $this->olist->getElement(0);
    }

    /**
     * testGetElements
     * @covers \pvc\struct\lists\ListOrdered::getElements
     */
    public function testGetElements(): void
    {
        $this->addElements(3);
        $expectedResult = [
            0 => $this->arrStrings[0],
            1 => $this->arrStrings[1],
            2 => $this->arrStrings[2],
        ];
        self::assertEquals($expectedResult, $this->olist->getElements());
    }

    /**
     * testUpdateThrowsExceptionWithStringTypeIndex
     * @covers \pvc\struct\lists\ListOrdered::update
     */
    public function testUpdateThrowsExceptionWithStringTypeIndex(): void
    {
        $this->addElements(2);
        $this->expectException(\TypeError::class);
        $this->olist->add('badkey', 'some value');
    }

    /**
     * testUpdateThrowsExceptionWithOutOfRangePositiveIndex
     * @covers \pvc\struct\lists\ListOrdered::update
     */
    public function testUpdateThrowsExceptionWithOutOfRangePositiveIndex(): void
    {
        $this->expectException(\OutOfRangeException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->add(4, 'some value');
    }

    /**
     * testUpdateThrowsExceptionWithOutOfRangeNegativeIndex
     * @covers \pvc\struct\lists\ListOrdered::update
     */
    public function testUpdateThrowsExceptionWithOutOfRangeNegativeIndex(): void
    {
        $this->expectException(\OutOfRangeException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->add(-2, 'some value');
    }

    /**
     * testUpdate
     * @covers \pvc\struct\lists\ListOrdered::update
     */
    public function testUpdate(): void
    {
        $this->addElements(2);
        $value_other = 'some other thing';
        $this->olist->update(1, $value_other);
        $value_1 = $this->olist->getElement(1);
        self::assertSame($value_other, $value_1);
    }

    /**
     * testDeleteExceptions
     * @covers \pvc\struct\lists\ListOrdered::delete
     */
    public function testDeleteTypeErrorException(): void
    {
        $this->addElements(3);
        $this->expectException(\TypeError::class);
        /** @phpstan-ignore-next-line */
        $this->olist->delete('badkey');
    }

    /**
     * testDeleteOutOfRangeException
     * @covers \pvc\struct\lists\ListOrdered::delete
     */
    public function testDeleteOutOfRangeException() : void
    {
        $this->expectException(\OutOfRangeException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->delete(-2);
    }

    /**
     * testDeleteThenAddInMiddle
     * @covers \pvc\struct\lists\ListOrdered::delete
     */
    public function testDeleteThenAddInMiddle(): void
    {
        $this->addElements(3);

        static::assertEquals(3, count($this->olist));

        $this->olist->delete(1);
        static::assertEquals(2, count($this->olist));

        // note the automatic reindexing
        static::assertEquals($this->arrStrings[2], $this->olist->getElement(1));

        $this->olist->add(1, $this->arrStrings[1]);
        // note the automatic reindexing
        static::assertEquals($this->arrStrings[2], $this->olist->getElement(2));
    }

    /**
     * testChangeIndexExceptionWhereOldIndexDoesNotExist
     * @covers \pvc\struct\lists\ListOrdered::changeIndex
     */
    public function testChangeIndexExceptionWhereOldIndexDoesNotExist(): void
    {
        $this->addElements(6);
        $this->expectException(NonExistentKeyException::class);
        $this->olist->changeIndex(10, 4);
    }

    /**
     * testChangeIndexExceptionWhereNewIndexDoesNotExist
     * @covers \pvc\struct\lists\ListOrdered::changeIndex
     */
    public function testChangeIndexExceptionWhereNewIndexDoesNotExist(): void
    {
        $this->addElements(6);
        $this->expectException(NonExistentKeyException::class);
        $this->olist->changeIndex(4, 10);
    }

    /**
     * testChangeIndexOne
     * @covers \pvc\struct\lists\ListOrdered::changeIndex
     */
    public function testChangeIndexOne(): void
    {
        $this->addElements(6);

        $this->olist->changeIndex(1, 3);
        static::assertEquals($this->arrStrings[2], $this->olist->getElement(1));
        static::assertEquals($this->arrStrings[3], $this->olist->getElement(2));
        static::assertEquals($this->arrStrings[1], $this->olist->getElement(3));
    }

    /**
     * testChangeIndexTwo
     * @covers \pvc\struct\lists\ListOrdered::changeIndex
     */
    public function testChangeIndexTwo(): void
    {
        $this->addElements(6);

        $this->olist->changeIndex(5, 3);
        static::assertEquals($this->arrStrings[3], $this->olist->getElement(4));
        static::assertEquals($this->arrStrings[4], $this->olist->getElement(5));
        static::assertEquals($this->arrStrings[5], $this->olist->getElement(3));
    }
}
