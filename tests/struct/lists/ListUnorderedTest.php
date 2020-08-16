<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\struct\lists\err\ListDuplicateKeyIndexException;
use pvc\struct\lists\err\ListNonExistentKeyIndexException;
use pvc\struct\lists\err\ListValidateOffsetException;
use pvc\struct\lists\ListUnordered;
use PHPUnit\Framework\TestCase;
use pvc\testingTraits\MockeryNonNegativeIntegerValidatorTrait;
use pvc\testingTraits\RandomStringGeneratorTrait;
use stdClass;

class ListUnorderedTest extends TestCase
{
    use MockeryNonNegativeIntegerValidatorTrait;
    use RandomStringGeneratorTrait;

    protected ListUnordered $ulist;
    protected array $arrStrings;
    protected int $badkey;

    public function setUp(): void
    {
        $this->badkey = -1;
        $this->ulist = new ListUnordered();
        $this->arrStrings = [];
    }


    public function addElements(int $n, int $stringLength = 10) : void
    {
        if ($n < 1) {
            $vars = [];
            $msgText = 'min number of elements to add is 1.';
            $msg = new ErrorExceptionMsg($vars, $msgText);
            $code = -1;
            throw new InvalidArgumentException($msg, $code);
        }

        for ($i = 0; $i < $n; $i++) {
            $this->arrStrings[$i] = $this->randomString($stringLength);
            $this->ulist->add($i, $this->arrStrings[$i]);
        }
    }

    public function testIsEmpty() : void
    {
        static::assertTrue($this->ulist->isEmpty());
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

    public function testGetElement() : void
    {
        $this->addElements(1);
        self::assertIsString($this->ulist->getElement(0));
    }

    public function testGetElementsException() : void
    {
        self::expectException(ListValidateOffsetException::class);
        $foo = $this->ulist->getElement($this->badkey);
    }

    public function testGetElements() : void
    {
        $this->addElements(2);

        $array = $this->ulist->getElements();
        static::assertTrue(is_array($array));
        static::assertEquals(2, count($array));
    }

    public function testGetKeysElements() : void
    {
        $this->addElements(2);

        $array = $this->ulist->getElements();
        static::assertTrue(is_array($array));
        $keys = array_keys($array);
        static::assertEquals($keys, $this->ulist->getKeys());

        static::assertEquals($this->arrStrings[0], $this->ulist->getElement(0));

        static::assertEquals(0, $keys[0]);
        static::assertEquals($this->arrStrings[0], $array[0]);

        static::assertEquals(1, $keys[1]);
        static::assertEquals($this->arrStrings[1], $array[1]);

        $found_value = $this->ulist->getElementByValue($this->arrStrings[1], [$this, 'callbackMatch']);
        static::assertEquals($found_value, $this->arrStrings[1]);

        $found_value = $this->ulist->getElementByValue('this is an impossible value to find', [$this, 'callbackMatch']);
        static::assertTrue(is_null($found_value));
    }

    public function testAddExceptionsWithBadKey() : void
    {
        $this->addElements(3);
        $this->expectException(ListValidateOffsetException::class);
        $this->ulist->add($this->badkey, 'some value');
    }

    public function testAddExceptionsWithDuplicateKey() : void
    {
        $this->addElements(3);
        $this->expectException(ListDuplicateKeyIndexException::class);
        $this->ulist->add(0, 'cannot add because key already exists');
    }

    public function testAdd() : void
    {
        $this->addElements(3);
        static::assertEquals(3, $this->ulist->count());
    }

    public function testUpdateExceptionsWithBadKey() : void
    {
        $this->addElements(3);
        $this->expectException(ListValidateOffsetException::class);
        $this->ulist->update($this->badkey, 'some value');
    }

    public function testUpdateExceptionsWithNonExistentKey() : void
    {
        $this->addElements(3);
        $this->expectException(ListNonExistentKeyIndexException::class);
        $this->ulist->update(4, 'some value');
    }

    public function testUpdate() : void
    {
        $this->addElements(3);

        $value_other = 'some other thing';
        $this->ulist->update(1, $value_other);

        $value_1 = $this->ulist->getElement(1);
        static::assertSame($value_other, $value_1);
    }

    public function testDeleteExceptions() : void
    {
        $this->addElements(3);

        $this->expectException(ListValidateOffsetException::class);
        $this->ulist->delete($this->badkey);

        $this->expectException(ListNonExistentKeyIndexException::class);
        $this->ulist->delete(4);
    }

    public function testDelete() : void
    {
        $this->addElements(3);

        $this->ulist->delete(1);
        static::assertEquals(2, count($this->ulist));

        static::assertTrue(is_null($this->ulist->getElement(1)));

        $this->expectException(ListNonExistentKeyIndexException::class);
        $this->ulist->delete(1);
    }

    public function testIterator() : void
    {
        $this->addElements(3);

        $values = $this->ulist->getElements();
        $i = 0;
        foreach ($this->ulist as $value) {
            static::assertEquals($value, $values[$i++]);
        }

        $this->ulist->rewind();
        static::assertEquals($this->arrStrings[0], $this->ulist->current());

        $this->ulist->next();
        static::assertEquals($this->arrStrings[1], $this->ulist->current());

        static::assertEquals(1, $this->ulist->key());

        $this->ulist->next();
        static::assertTrue($this->ulist->valid());
        $this->ulist->next();
        static::assertFalse($this->ulist->valid());
    }

    public function testCountable() : void
    {
        static::assertEquals(0, count($this->ulist));

        $this->addElements(3);
        static::assertEquals(3, $this->ulist->count());
        static::assertEquals(3, count($this->ulist));
    }


    public function testArrayAccess() : void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->arrStrings[$i] = $this->randomString(10);
        }

        $otherValue = 'some other string';

        // offsetSet used to add elements
        $this->ulist[0] = $this->arrStrings[0];
        $this->ulist[1] = $this->arrStrings[1];
        // null index results in appending to the list - index should be 2
        $this->ulist[] = $otherValue;

        // offsetSet used to update an element
        $this->ulist[0] = $otherValue;
        static::assertEquals($otherValue, $this->ulist[0]);

        // offsetExists
        static::assertTrue(isset($this->ulist[0]));
        static::assertTrue(isset($this->ulist[2]));
        static::assertFalse(isset($this->ulist[3]));

        // offsetUnset
        unset($this->ulist[1]);
        static::assertFalse(isset($this->ulist[1]));
    }

    public function testArrayAccessOffsetExistsException() : void
    {
        $input = new stdClass();
        self::expectException(ListValidateOffsetException::class);
        $foo = isset($this->ulist[$input]);
    }

    public function testArrayAccessOffsetGetException() : void
    {
        $input = new stdClass();
        self::expectException(ListValidateOffsetException::class);
        $foo = $this->ulist[$input];
    }

    public function testArrayAccessOffsetSetException() : void
    {
        $input = new stdClass();
        self::expectException(ListValidateOffsetException::class);
        $this->ulist[$input] = 'nine';
    }

    public function testArrayAccessOffsetUnsetException() : void
    {
        $input = new stdClass();
        self::expectException(ListValidateOffsetException::class);
        unset($this->ulist[$input]);
    }

    public function testPushEmptyList() : void
    {
        $this->ulist->push('some value');
        static::assertEquals(1, count($this->ulist));
        static::assertTrue($this->ulist->offsetExists(0));
    }

    public function testPushPopulatedList() : void
    {
        $this->addElements(3);
        $this->ulist->push('some value');
        static::assertEquals(4, count($this->ulist));
        static::assertTrue($this->ulist->offsetExists(3));
    }
}
