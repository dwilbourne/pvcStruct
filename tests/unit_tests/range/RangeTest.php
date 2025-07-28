<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\range;

use PHPUnit\Framework\TestCase;
use pvc\struct\range\Range;

class RangeTest extends TestCase
{
    protected Range $range;

    public function setUp(): void
    {
        $this->range = $this->getMockForAbstractClass(Range::class);
    }

    /**
     * testSetGetRange
     *
     * @covers \pvc\struct\range\Range::setRange()
     * @covers \pvc\struct\range\Range::getRange()
     */
    public function testSetGetRange(): void
    {
        $min = 3;
        $max = 8;
        $this->range->setRange($min, $max);
        $this->range->method('getMin')->willReturn($min);
        $this->range->method('getMax')->willReturn($max);
        $expectedResult = [$min, $max];
        self::assertEqualsCanonicalizing(
            $expectedResult,
            $this->range->getRange()
        );
    }


    public function isInRangeDataProvider(): array
    {
        $min = 0;
        $max = 5;
        return [
            [$min, $max, -1, false],
            [$min, $max, $min, true],
            [$min, $max, 2, true],
            [$min, $max, $max, true],
            [$min, $max, 7, false],
        ];
    }

    /**
     * testIsInRange
     *
     * @param $min
     * @param $max
     * @param $value
     * @param $expectedResult
     *
     * @dataProvider isInRangeDataProvider
     * @covers       \pvc\struct\range\Range::isInRange
     */
    public function testIsInRange($min, $max, $value, $expectedResult): void
    {
        $this->range->method('getMin')->willReturn($min);
        $this->range->method('getMax')->willReturn($max);
        self::assertEquals($expectedResult, $this->range->isInRange($value));
    }
}
