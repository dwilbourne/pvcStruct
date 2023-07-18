<?php

declare (strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace pvcTests\struct\range_collection\type_manager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\range_collection\type_manager\AbstractTypeManager;

class AbstractTypeManagerTest extends TestCase
{
	/**
	 * @var AbstractTypeManager&MockObject
	 */
	protected AbstractTypeManager $mock;

	public function setUp() : void
	{
		$this->mock = $this->getMockForAbstractClass(AbstractTypeManager::class);
	}

	/**
	 * testCompareData
	 * @covers \pvc\struct\range_collection\type_manager\AbstractTypeManager::compareData
	 */
	public function testCompareData() : void
	{
		$a = 5;
		$b = 6;
		self::assertEquals(-1, $this->mock->compareData($a, $b));
		self::assertEquals(0, $this->mock->compareData($a, $a));
		self::assertEquals(1, $this->mock->compareData($b, $a));
	}
}
