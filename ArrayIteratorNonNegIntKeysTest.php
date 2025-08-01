<?php

namespace pvcTests\struct\unit_tests\collection;

use pvc\struct\collection\ArrayIteratorNonNegIntKeys;
use PHPUnit\Framework\TestCase;

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
    }
}
