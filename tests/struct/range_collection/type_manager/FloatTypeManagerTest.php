<?php

declare(strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace tests\struct\range_collection\type_manager;

use DateTimeImmutable;
use pvc\struct\range_collection\type_manager\FloatTypeManager;
use PHPUnit\Framework\TestCase;

class FloatTypeManagerTest extends TestCase
{
    /**
     * @var FloatTypeManager
     */
    protected FloatTypeManager $typeManager;

    public function setUp(): void
    {
        $this->typeManager = new FloatTypeManager();
    }

    public function dataProvider(): array
    {
        return [
            [5.412, true],
            ["foo", false],
            // DateTime is not a float
            [new DateTimeImmutable(), false],
            // int is OK
            [5, true],
            // floats and doubles are the same in PHP
            [(float) 5, true],
        ];
    }

    /**
     * testDataTypes
     * @param $data
     * @param $expectedResult
     * @dataProvider dataProvider
     * @covers \pvc\struct\range_collection\type_manager\FloatTypeManager::validateDataType
     */
    public function testDataTypes($data, $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->typeManager->validateDataType($data));
    }
}
