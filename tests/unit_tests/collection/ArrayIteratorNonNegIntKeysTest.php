<?php

namespace pvcTests\struct\unit_tests\collection;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\ArrayIteratorNonNegIntKeys;

use pvc\struct\collection\err\InvalidKeyException;

use function PHPUnit\Framework\assertEquals;

class ArrayIteratorNonNegIntKeysTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\collection\ArrayIteratorNonNegIntKeys::__construct
     */
    public function testConstruct(): void
    {
        $array = [0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd', 4 => 'e'];
        $arrayIterator = new ArrayIteratorNonNegIntKeys($array);
        self::assertInstanceOf(ArrayIteratorNonNegIntKeys::class, $arrayIterator);
        $result = [];
        foreach ($arrayIterator as $key => $value) {
            $result[$key] = $value;
        }
        assertEquals($array, $result);
    }

    /**
     * @return void
     * @covers \pvc\struct\collection\ArrayIteratorNonNegIntKeys::validateKey
     */
    public function testStringKeyThrowsException(): void
    {
        $badKey = 'foo';
        $array = [$badKey => 'a'];
        self::expectException(InvalidKeyException::class);
        new ArrayIteratorNonNegIntKeys($array);
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\ArrayIteratorNonNegIntKeys::offsetSet
     */
    public function testOffsetSetThrowsExceptionWithBadKey(): void
    {
        $iterator = new ArrayIteratorNonNegIntKeys();
        $this->expectException(InvalidKeyException::class);
        $iterator->offsetSet(-1, 'a');
    }

    /**
     * @return void
     * @throws InvalidKeyException
     * @covers \pvc\struct\collection\ArrayIteratorNonNegIntKeys::offsetSet
     */
    public function testOffsetSetSucceeds(): void
    {
        $iterator = new ArrayIteratorNonNegIntKeys();
        $key = 1;
        $value = 'a';
        $iterator->offsetSet($key, $value);
        assertEquals($value, $iterator->offsetGet($key));

    }
}
