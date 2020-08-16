<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection;

use Mockery;
use pvc\struct\range_collection\range_element\RangeElementInteger;
use pvc\struct\range_collection\RangeCollectionInteger;

/**
 * Class RangeCollectionIntegerTest
 * @package tests\struct\range_collection
 */
class RangeCollectionIntegerTest extends RangeCollectionTestCase
{
    public function setUp() : void
    {
        $this->rangeCollection = new RangeCollectionInteger();
        $this->rangeElementA = Mockery::mock(RangeElementInteger::class);
        $this->rangeElementB = Mockery::mock(RangeElementInteger::class);
    }
}
