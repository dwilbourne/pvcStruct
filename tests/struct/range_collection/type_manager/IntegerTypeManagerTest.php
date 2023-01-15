<?php

declare (strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace tests\struct\range_collection\type_manager;

use DateTimeImmutable;
use pvc\struct\range_collection\type_manager\IntegerTypeManager;
use PHPUnit\Framework\TestCase;

class IntegerTypeManagerTest extends TestCase
{
	/**
	 * @var IntegerTypeManager
	 */
	protected IntegerTypeManager $typeManager;

	public function setUp(): void
	{
		$this->typeManager = new IntegerTypeManager();
	}

	public function dataProvider(): array
	{
		return [
			[5.412, false],
			["foo", false],
			// DateTime is not a float
			[new DateTimeImmutable(), false],
			// int is OK
			[5, true],
			// "syntactic floats that are integers" are not ok
			[5.0, false],
		];
	}

	/**
	 * testDataTypes
	 * @param $data
	 * @param $expectedResult
	 * @dataProvider dataProvider
	 * @covers \pvc\struct\range_collection\type_manager\IntegerTypeManager::validateDataType
	 */
	public function testDataTypes($data, $expectedResult): void
	{
		self::assertEquals($expectedResult, $this->typeManager->validateDataType($data));
	}
}
