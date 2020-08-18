<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element_factory;

use Carbon\Carbon;
use pvc\struct\range_collection\range_element_factory\RangeElementCarbonFactory;

/**
 * Class RangeElementCarbonFactoryTest
 * @package tests\struct\range_collection\range_element_factory
 */
class RangeElementCarbonFactoryTest extends RangeElementFactoryTestCase
{
    public function setUp() : void
    {
        $this->min = new Carbon('2015-01-01');
        $this->max = new Carbon('2020-12-31');
        $this->rangeElementFactory = new RangeElementCarbonFactory();
    }
}
