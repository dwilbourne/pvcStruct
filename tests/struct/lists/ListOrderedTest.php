<?php
namespace tests\struct\lists;

use PHPUnit\Framework\TestCase;
use pvc\err\throwable\exception\pvc_exceptions\InvalidArrayIndexException;
use pvc\msg\ErrorExceptionMsg;
use pvc\struct\lists\err\ListDuplicateKeyIndexException;
use pvc\struct\lists\err\ListNonExistentKeyIndexException;
use pvc\struct\lists\err\ListValidateOffsetException;
use pvc\struct\lists\ListOrdered;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\testingTraits\MockeryNonNegativeIntegerValidatorTrait;
use pvc\testingTraits\RandomStringGeneratorTrait;

class ListOrderedTest extends TestCase
{
    use MockeryNonNegativeIntegerValidatorTrait;
    use RandomStringGeneratorTrait;
    
    protected ListOrdered $olist;
    protected array $arrStrings;

    public function setUp(): void
    {
        $this->olist = new ListOrdered();
        $this->arrStrings = [];
    }

    public function addElements(int $n, int $stringLength = 10) : void
    {
        if ($n < 1) {
            $msgText = 'min number of elements to add is 1.';
            $msg = new ErrorExceptionMsg([], $msgText);
            throw new InvalidArgumentException($msg);
        }

        for ($i = 0; $i < $n; $i++) {
            $this->arrStrings[$i] = $this->randomString($stringLength);
            $this->olist->push($this->arrStrings[$i]);
        }
    }

    public function testIsEmpty() : void
    {
        static::assertTrue($this->olist->isEmpty());
    }

    /**
     * @function callbackMatch
     * @param mixed $value_1
     * @param mixed $value_2
     * @return bool
     */
    public function callbackMatch($value_1, $value_2)
    {
        return ($value_1 == $value_2);
    }

    public function testGetKeysElements() : void
    {
        $this->addElements(2);

        $array = $this->olist->getElements();
        static::assertTrue(is_array($array));
        $arrayKeys = array_keys($array);
        static::assertEquals($arrayKeys, $this->olist->getKeys());

        static::assertEquals($this->arrStrings[0], $this->olist->getElement(0));

        static::assertEquals(0, $arrayKeys[0]);
        static::assertEquals($this->arrStrings[0], $array[0]);

        static::assertEquals(1, $arrayKeys[1]);
        static::assertEquals($this->arrStrings[1], $array[1]);

        $found_value = $this->olist->getElementByValue($this->arrStrings[1], [$this, 'callbackMatch']);
        static::assertEquals($found_value, $this->arrStrings[1]);

        $v = 'this is an impossible value to find because string lengths are 10 chars';
        $found_value = $this->olist->getElementByValue($v, [$this, 'callbackMatch']);
        static::assertTrue(is_null($found_value));
    }

    public function testGetElements() : void
    {
        $this->addElements(3);
        $this->olist->add(1, 'some string');
        $expectedResult = [
            0 => $this->arrStrings[0],
            1 => 'some string',
            2 => $this->arrStrings[1],
            3 => $this->arrStrings[2]
        ];
        self::assertEquals($expectedResult, $this->olist->getElements());
    }

    public function testCountable() : void
    {
        static::assertTrue(0 == count($this->olist));

        $this->addElements(3);

        static::assertTrue(3 == $this->olist->count());
        static::assertTrue(3 == count($this->olist));
    }

    public function testAddExceptions() : void
    {
        $this->addElements(3);

        $this->expectException(ListValidateOffsetException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->add('badkey', 'some value');

        $this->expectException(ListValidateOffsetException::class);
        $this->olist->add(4, 'cannot add because key is out of range');

        $this->expectException(ListValidateOffsetException::class);
        $this->olist->add(-2, 'keys must be non-negative');
    }

    public function testAdd() : void
    {
        $this->addElements(3);
        $someValue = 'inserted at position 0';
        $this->olist->add(0, $someValue);
        static::assertEquals($this->olist[0], $someValue);
        static::assertEquals($this->olist[1], $this->arrStrings[0]);
    }

    public function testUpdateException() : void
    {
        $this->addElements(2);
        $this->expectException(ListValidateOffsetException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->update('badkey', 'some value');
    }

    public function testUpdate() : void
    {
        $this->addElements(2);
        $value_other = 'some other thing';
        $this->olist->update(1, $value_other);
        $value_1 = $this->olist->getElement(1);
        static::assertSame($value_other, $value_1);
    }

    public function testDeleteExceptions() : void
    {
        $this->addElements(3);
        $this->expectException(InvalidArrayIndexException::class);
        /** @phpstan-ignore-next-line */
        $this->olist->delete('badkey');
    }

    public function testDeleteThenAddInMiddle() : void
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

    public function testIterator() : void
    {
        $this->addElements(3);

        $values = $this->olist->getElements();
        $i = 0;
        foreach ($this->olist as $value) {
            static::assertEquals($value, $values[$i++]);
        }

        $this->olist->rewind();
        static::assertEquals($this->arrStrings[0], $this->olist->current());

        $this->olist->next();
        static::assertEquals(1, $this->olist->key());
        static::assertEquals($this->arrStrings[$this->olist->key()], $this->olist->current());

        $this->olist->next();
        static::assertTrue($this->olist->valid());
        $this->olist->next();
        static::assertFalse($this->olist->valid());
    }

    public function testArrayAccessExceptions() : void
    {
        $this->addElements(2);

        $this->expectException(ListValidateOffsetException::class);
        $this->olist[-1] = 'bar';

        $this->expectException(ListValidateOffsetException::class);
        /** @phpstan-ignore-next-line */
        $this->olist['badIndex'] = 'bar';
    }

    public function testArrayAccess() : void
    {
        $this->addElements(2);

        // offsetSet used to add
        $newString = 'This is some new string.';
        $this->olist[] = $newString;
        static::assertEquals(3, count($this->olist));

        // offsetSet used to update - it cannot be used to add
        $this->olist[0] = $this->arrStrings[1];
        $this->olist[1] = $this->arrStrings[0];
        static::assertEquals($this->olist[1], $this->arrStrings[0]);
        static::assertEquals($this->olist[0], $this->arrStrings[1]);

        // offsetExists
        static::assertTrue(isset($this->olist[0]));
        static::assertFalse(isset($this->olist[3]));

        // offsetUnset.  The list is reindexed
        unset($this->olist[1]);
        static::assertEquals($newString, $this->olist[1]);
        static::assertFalse(isset($this->olist[2]));
    }

    public function testArrayAccessUnsetExceptions() : void
    {
        $this->addElements(2);
        $this->expectException(ListNonExistentKeyIndexException::class);
        unset($this->olist[4]);
    }


    public function testPush() : void
    {
        $value = 'some value';
        $this->olist->push($value);
        static::assertEquals($value, $this->olist->getElement(count($this->olist) - 1));
    }

    public function testChangeIndexExceptionWhereOldIndexEqualsNewIndex() : void
    {
        $this->addElements(6);
        $this->expectException(ListDuplicateKeyIndexException::class);
        $this->olist->changeIndex(4, 4);
    }

    public function testChangeIndexExceptionWhereOldIndexDoesNotExist() : void
    {
        $this->addElements(6);
        $this->expectException(ListNonExistentKeyIndexException::class);
        $this->olist->changeIndex(10, 4);
    }

    public function testChangeIndexExceptionWhereNewIndexDoesNotExist() : void
    {
        $this->addElements(6);
        $this->expectException(ListNonExistentKeyIndexException::class);
        $this->olist->changeIndex(4, 10);
    }

    public function testChangeIndexOne() : void
    {
        $this->addElements(6);

        $this->olist->changeIndex(1, 3);
        static::assertEquals($this->arrStrings[2], $this->olist->getElement(1));
        static::assertEquals($this->arrStrings[3], $this->olist->getElement(2));
        static::assertEquals($this->arrStrings[1], $this->olist->getElement(3));
    }

    public function testChangeIndexTwo() : void
    {
        $this->addElements(6);

        $this->olist->changeIndex(5, 3);
        static::assertEquals($this->arrStrings[3], $this->olist->getElement(4));
        static::assertEquals($this->arrStrings[4], $this->olist->getElement(5));
        static::assertEquals($this->arrStrings[5], $this->olist->getElement(3));
    }
}
