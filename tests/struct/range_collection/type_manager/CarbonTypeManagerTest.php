<?php

declare(strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace tests\struct\range_collection\type_manager;

use Carbon\Carbon;
use pvc\struct\range_collection\type_manager\CarbonTypeManager;
use PHPUnit\Framework\TestCase;

class CarbonTypeManagerTest extends TestCase
{
    /**
     * @var CarbonTypeManager
     */
    protected CarbonTypeManager $typeManager;

    public function setUp(): void
    {
		$this->typeManager = new CarbonTypeManager();
    }

	public function dataProvider() : array
	{
		return [
			[new Carbon("12/25/2022"), true],
			["foo", false],
			// DateTime is not an instance of Carbon
			[new \DateTimeImmutable(), false],
			[true, false],
		];
	}

	/**
	 * testDataTypes
	 * @param $data
	 * @param $expectedResult
	 * @dataProvider dataProvider
	 * @covers \pvc\struct\range_collection\type_manager\CarbonTypeManager::validateDataType
	 */
	public function testDataTypes($data, $expectedResult) : void
	{
		self::assertEquals($expectedResult, $this->typeManager->validateDataType($data));
	}
}
