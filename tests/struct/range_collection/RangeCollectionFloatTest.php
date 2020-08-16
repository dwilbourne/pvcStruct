<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection;

use Mockery;
use pvc\struct\range_collection\range_element\RangeElementFloat;
use pvc\struct\range_collection\RangeCollectionFloat;

/**
 * Class RangeCollectionFloatTest
 * @package tests\struct\range_collection
 */
class RangeCollectionFloatTest extends RangeCollectionTestCase
{
    public function setUp() : void
    {
        $this->rangeCollection = new RangeCollectionFloat();
        $this->rangeElementA = Mockery::mock(RangeElementFloat::class);
        $this->rangeElementB = Mockery::mock(RangeElementFloat::class);
    }
}
