<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\range;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\range\Range;

class RangeTest extends TestCase
{
    protected Range|MockObject $mockRange;

    public function setUp(): void
    {
        $this->mockRange = $this->getMockBuilder(Range::class)
                                ->disableOriginalConstructor()
                                ->getMockForAbstractClass();
    }

    /**
     * testConstruct
     * @covers \pvc\struct\range\Range::__construct
     * @covers \pvc\struct\range\Range::setMin
     * @covers \pvc\struct\range\Range::setMax
     */
    public function testConstructStandardMinMax(): void
    {
        $min = 3;
        $max = 8;
        $concreteClass = $this->makeConcreteClass($min, $max);
        self::assertInstanceOf(Range::class, $concreteClass);
        self::assertEquals($min, $concreteClass->getMin());
        self::assertEquals($max, $concreteClass->getMax());
    }

    protected function makeConcreteClass($min, $max): Range
    {
        return new class($min, $max) extends Range {
            public function getMin(): mixed
            {
                return $this->min;
            }

            public function getMax(): mixed
            {
                return $this->max;
            }
        };
    }

    /**
     * testConstructMinMaxReversed
     * @covers \pvc\struct\range\Range::__construct
     */
    public function testConstructMinMaxReversed(): void
    {
        $min = 8;
        $max = 3;
        $concreteClass = $this->makeConcreteClass($min, $max);
        self::assertEquals($max, $concreteClass->getMin());
        self::assertEquals($min, $concreteClass->getMax());
    }

    /**
     * testGetRange
     * @covers \pvc\struct\range\Range::getRange
     */
    public function testGetRange(): void
    {
        $min = 8;
        $max = 3;
        $this->mockRange->method('getMin')->willReturn($min);
        $this->mockRange->method('getMax')->willReturn($max);
        self::assertIsArray($this->mockRange->getRange());
        self::assertEquals(2, count($this->mockRange->getRange()));
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
     * @param $min
     * @param $max
     * @param $value
     * @param $expectedResult
     * @dataProvider isInRangeDataProvider
     * @covers       \pvc\struct\range\Range::isInRange
     */
    public function testIsInRange($min, $max, $value, $expectedResult): void
    {
        $concreteClass = $this->makeConcreteClass($min, $max);
        self::assertEquals($expectedResult, $concreteClass->isInRange($value));
    }
}
