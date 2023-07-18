<?php

declare (strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace pvcTests\struct\range_collection\type_manager;

use DateTime;
use PHPUnit\Framework\TestCase;
use pvc\struct\range_collection\type_manager\DateTimeTypeManager;

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
