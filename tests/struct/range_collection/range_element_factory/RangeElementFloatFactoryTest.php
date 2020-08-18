<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element_factory;

use pvc\struct\range_collection\range_element_factory\RangeElementFloatFactory;

/**
 * Class RangeElementFloatFactoryTest
 * @package tests\struct\range_collection\range_element_factory
 */
class RangeElementFloatFactoryTest extends RangeElementFactoryTestCase
{
    public function setUp() : void
    {
        $this->min = 5.4321;
        $this->max = 7.6543;
        $this->rangeElementFactory = new RangeElementFloatFactory();
    }
}
