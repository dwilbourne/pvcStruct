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
    /**
     * testSetGetMinSetGetMax
     * @covers \pvc\struct\range\RangeFloat::getMin
     * @covers \pvc\struct\range\RangeFloat::getMax
     */
    public function testSetGetMinSetGetMax(): void
    {
        $min = 5.3;
        $max = 15.2;
        $rangeElement = new RangeFloat($min, $max);
        self::assertEquals($min, $rangeElement->getMin());
        self::assertEquals($max, $rangeElement->getMax());
    }

}
