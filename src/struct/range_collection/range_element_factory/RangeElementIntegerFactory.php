<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element_factory;

use pvc\struct\range_collection\range_element\RangeElementInteger;

/**
 * Class RangeElementIntegerFactory
 * @package pvc\struct\range_collection\range_element_factory
 */
class RangeElementIntegerFactory implements RangeElementFactoryInterface
{
    public function createRangeElement($min, $max) : RangeElementInteger
    {
        return new RangeElementInteger($min, $max);
    }
}
