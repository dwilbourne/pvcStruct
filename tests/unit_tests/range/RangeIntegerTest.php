<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\range;

use PHPUnit\Framework\TestCase;
use pvc\struct\range\RangeInteger;

class RangeIntegerTest extends TestCase
{
    protected RangeInteger $range;

    public function setUp(): void
    {
        $this->range = new RangeInteger();
    }

    /**
     * testDefaultMinMax
     *
     * @covers \pvc\struct\range\RangeInteger::getMin
     * @covers \pvc\struct\range\RangeInteger::getMax
     */
    public function testDefaultMinMax(): void
    {
        $array = $this->range->getRange();
        self::assertEquals(PHP_INT_MIN, $array[0]);
        self::assertEquals(PHP_INT_MAX, $array[1]);
    }

    /**
     * testGetMinGetMax
     *
     * @covers \pvc\struct\range\RangeInteger::getMin
     * @covers \pvc\struct\range\RangeInteger::getMax
     */
    public function testGetMinGetMax(): void
    {
        $min = 3;
        $max = 8;
        $this->range->setRange($min, $max);
        $expectedResult = [$min, $max];
        self::assertEqualsCanonicalizing(
            $expectedResult,
            $this->range->getRange()
        );
    }
}
