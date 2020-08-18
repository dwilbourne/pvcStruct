<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_collection_factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\range_collection\RangeCollectionInterface;

/**
 * Class RangeCollectionFactoryTestCase
 * @package tests\struct\range_collection\range_collection_factory
 */
class RangeCollectionFactoryTestCase extends TestCase
{
    protected $rangeCollectionFactory;

    public function testCreateRangeCollection() : void
    {
        self::assertInstanceOf(
            RangeCollectionInterface::class,
            $this->rangeCollectionFactory->createRangeCollection()
        );
    }
}
