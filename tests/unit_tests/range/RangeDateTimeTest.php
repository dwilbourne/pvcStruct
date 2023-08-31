<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\range;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use pvc\struct\range\RangeDateTime;

class RangeDateTimeTest extends TestCase
{
    /**
     * testSetGetMinSetGetMax
     * @covers \pvc\struct\range\RangeDateTime::getMin
     * @covers \pvc\struct\range\RangeDateTime::getMax
     */
    public function testSetGetMinSetGetMax(): void
    {
        $min = new DateTimeImmutable();
        $min->setDate(2023, 7, 27);
        $max = new DateTimeImmutable();
        $max->setDate(2024, 7, 26);
        $rangeElement = new RangeDateTime($min, $max);
        self::assertEquals($min, $rangeElement->getMin());
        self::assertEquals($max, $rangeElement->getMax());
    }
}
