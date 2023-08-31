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
    /**
     * testSetGetMinSetGetMax
     * @covers \pvc\struct\range\RangeInteger::getMin
     * @covers \pvc\struct\range\RangeInteger::getMax
     */
    public function testSetGetMinSetGetMax(): void
    {
        $min = 4;
        $max = 10;
        $rangeElement = new RangeInteger($min, $max);
        self::assertEquals($min, $rangeElement->getMin());
        self::assertEquals($max, $rangeElement->getMax());
    }
}
