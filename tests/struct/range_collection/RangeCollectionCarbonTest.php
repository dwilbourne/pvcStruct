<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection;

use Mockery;
use pvc\struct\range_collection\range_element\RangeElementCarbon;
use pvc\struct\range_collection\RangeCollectionCarbon;

/**
 * Class RangeCollectionCarbonTest
 * @package tests\struct\range_collection
 */
class RangeCollectionCarbonTest extends RangeCollectionTestCase
{
    public function setUp() : void
    {
        $this->rangeCollection = new RangeCollectionCarbon();
        $this->rangeElementA = Mockery::mock(RangeElementCarbon::class);
        $this->rangeElementB = Mockery::mock(RangeElementCarbon::class);
    }
}
