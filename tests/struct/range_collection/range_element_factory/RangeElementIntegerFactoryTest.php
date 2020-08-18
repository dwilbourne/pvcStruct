<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element_factory;

use pvc\struct\range_collection\range_element_factory\RangeElementIntegerFactory;

/**
 * Class RangeElementIntegerFactoryTest
 * @package tests\struct\range_collection\range_element_factory
 */
class RangeElementIntegerFactoryTest extends RangeElementFactoryTestCase
{
    public function setUp() : void
    {
        $this->min = 5;
        $this->max = 7;
        $this->rangeElementFactory = new RangeElementIntegerFactory();
    }
}
