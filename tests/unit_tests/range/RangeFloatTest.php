<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\range;

use PHPUnit\Framework\TestCase;
use pvc\struct\range\RangeFloat;

class RangeFloatTest extends TestCase
{
    protected RangeFloat $range;

    public function setUp(): void
    {
        $this->range = new RangeFloat();
    }

    /**
     * testDefaultMinMax
     * @covers \pvc\struct\range\RangeFloat::getMin
     * @covers \pvc\struct\range\RangeFloat::getMax
     */
    public function testDefaultMinMax(): void
    {
        $array = $this->range->getRange();
        self::assertEquals(PHP_FLOAT_MIN, $array[0]);
        self::assertEquals(PHP_FLOAT_MAX, $array[1]);
    }

    /**
     * testGetMinGetMax
     * @covers \pvc\struct\range\RangeFloat::getMin
     * @covers \pvc\struct\range\RangeFloat::getMax
     */
    public function testGetMinGetMax(): void
    {
        $min = 3.5;
        $max = 8.9;
        $this->range->setRange($min, $max);
        $expectedResult = [$min, $max];
        self::assertEqualsCanonicalizing($expectedResult, $this->range->getRange());
    }
}
