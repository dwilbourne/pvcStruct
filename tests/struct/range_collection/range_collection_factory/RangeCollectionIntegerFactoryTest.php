<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_collection_factory;

use pvc\struct\range_collection\range_collection_factory\RangeCollectionIntegerFactory;

/**
 * Class RangeCollectionIntegerFactoryTest
 * @package tests\struct\range_collection\range_collection_factory
 */
class RangeCollectionIntegerFactoryTest extends RangeCollectionFactoryTestCase
{
    public function setUp() : void
    {
        $this->rangeCollectionFactory = new RangeCollectionIntegerFactory();
    }
}
