<?php

declare(strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace pvcTests\struct\range_collection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;
use pvc\struct\range_collection\RangeCollection;

class RangeCollectionTest extends TestCase
{
    /**
     * @var RangeCollection
     */
    protected RangeCollection $collection;

    /**
     * @var RangeCollectionTypeManagerInterface&MockObject
     */
    protected RangeCollectionTypeManagerInterface $typeManager;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->typeManager = $this->createMock(RangeCollectionTypeManagerInterface::class);
        $this->collection = new RangeCollection($this->typeManager);
    }

	/**
	 * testSetGetTypeManager
	 * @covers \pvc\struct\range_collection\RangeCollection::setTypeManager
	 * @covers \pvc\struct\range_collection\RangeCollection::getTypeManager
	 */
    public function testSetGetTypeManager(): void
    {
        $newTypeManager = $this->createStub(RangeCollectionTypeManagerInterface::class);
        $this->collection->setTypeManager($newTypeManager);
        self::assertSame($newTypeManager, $this->collection->getTypeManager());
    }

	/**
	 * testConstruct
	 * @covers \pvc\struct\range_collection\RangeCollection::__construct
	 */
	public function testConstruct() : void
	{
		$typeManager = $this->createStub(RangeCollectionTypeManagerInterface::class);
		$collection = new RangeCollection($typeManager);
		self::assertEquals($typeManager, $collection->getTypeManager());
	}

	/**
	 * testAddRangeSuccessfulBaseCase
	 * @covers \pvc\struct\range_collection\RangeCollection::addRange
	 */
    public function testAddRangeSuccessfulBaseCase(): void
    {
        $argFirst = 5;
        $argSecond = 10;
        $this->typeManager->expects($this->exactly(2))
                          ->method('validateDataType')
                          ->withConsecutive([$argFirst], [$argSecond])
                            ->willReturnOnConsecutiveCalls(true, true);
        $this->typeManager->expects($this->once())
                          ->method('compareData')
                          ->with($argFirst, $argSecond)
                          ->willReturn(-1);
        self::assertTrue($this->collection->addRange($argFirst, $argSecond));
    }

	/**
	 * testAddRangeSuccessWithNullSecondArgument
	 * @covers \pvc\struct\range_collection\RangeCollection::addRange
	 */
    public function testAddRangeSuccessWithNullSecondArgument(): void
    {
        $argFirst = 5;
        $argSecond = null;
        $this->typeManager->expects($this->exactly(2))
                          ->method('validateDataType')
                          ->withConsecutive([$argFirst], [$argFirst])
                          ->willReturnOnConsecutiveCalls(true, true);
        $this->typeManager->expects($this->once())
                          ->method('compareData')
                          ->with($argFirst, $argFirst)
                          ->willReturn(0);
        self::assertTrue($this->collection->addRange($argFirst, $argSecond));
        // demonstrate that the max value is also the min value
        $array = $this->collection->getRanges();
        $element = $array[0];
        self::assertEquals($element[0], $element[1]);
    }

	/**
	 * testAddRangeWithDissimilarDataTypesReturnsFalse
	 * @covers \pvc\struct\range_collection\RangeCollection::addRange
	 */
    public function testAddRangeWithDissimilarDataTypesReturnsFalse(): void
    {
        $argFirst = 5;
        $argSecond = "foo";
        $this->typeManager->expects($this->exactly(2))
                          ->method('validateDataType')
                         ->withConsecutive([$argFirst], [$argSecond])
                          ->willReturnOnConsecutiveCalls(true, false);
        self::assertFalse($this->collection->addRange($argFirst, $argSecond));
    }

	/**
	 * testAddRangeReversesArgsInCollectionElementIfSecondGreaterThanFirst
	 * @covers \pvc\struct\range_collection\RangeCollection::addRange
	 */
    public function testAddRangeReversesArgsInCollectionElementIfSecondGreaterThanFirst(): void
    {
        $argFirst = 10;
        $argSecond = 5;
        $this->typeManager->expects($this->exactly(2))
                          ->method('validateDataType')
                          ->withConsecutive([$argFirst], [$argSecond])
                          ->willReturnOnConsecutiveCalls(true, true);
        $this->typeManager->expects($this->once())
                          ->method('compareData')
                          ->with($argFirst, $argSecond)
                          ->willReturn(1);
        self::assertTrue($this->collection->addRange($argFirst, $argSecond));
        $array = $this->collection->getRanges();
        $element = $array[0];
        self::assertEquals($argSecond, $element[0]);
        self::assertEquals($argFirst, $element[1]);
    }

	/**
	 * testGetRangesSortsReturnElementsByMinValueOfEachElementInAscendingOrder
	 * @covers \pvc\struct\range_collection\RangeCollection::getRanges
	 */
    public function testGetRangesSortsReturnElementsByMinValueOfEachElementInAscendingOrder(): void
    {
        $argFirst = 5;
        $argSecond = 10;
        $argThird = 1;
        $argFourth = 11;
        $expectedArray = [[1, 11], [5, 10]];

        $stub = $this->createStub(RangeCollectionTypeManagerInterface::class);
        $this->collection->setTypeManager($stub);
        $stub->method('validateDataType')->willReturn(true);
        $stub->method('compareData')->willReturn(-1);
        $this->collection->addRange($argFirst, $argSecond);
        $this->collection->addRange($argThird, $argFourth);
        self::assertEqualsCanonicalizing($expectedArray, $this->collection->getRanges());
    }
}
