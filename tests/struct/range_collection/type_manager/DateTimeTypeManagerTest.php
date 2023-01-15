<?php

declare (strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace tests\struct\range_collection\type_manager;

use Carbon\Carbon;
use DateTime;
use pvc\struct\range_collection\type_manager\DateTimeTypeManager;
use PHPUnit\Framework\TestCase;

class DateTimeTypeManagerTest extends TestCase
{
	/**
	 * @var DateTimeTypeManager
	 */
	protected DateTimeTypeManager $typeManager;

	public function setUp(): void
	{
		$this->typeManager = new DateTimeTypeManager();
	}

	public function dataProvider() : array
	{
		return [
			[new DateTime("12/25/2022"), true],
			["foo", false],
			// Carbon is an instance of DateTime!
			[new Carbon(), true],
			[true, false],
		];
	}

	/**
	 * testDataTypes
	 * @param $data
	 * @param $expectedResult
	 * @dataProvider dataProvider
	 * @covers \pvc\struct\range_collection\type_manager\DateTimeTypeManager::validateDataType
	 */
	public function testDataTypes($data, $expectedResult) : void
	{
		self::assertEquals($expectedResult, $this->typeManager->validateDataType($data));
	}

}
