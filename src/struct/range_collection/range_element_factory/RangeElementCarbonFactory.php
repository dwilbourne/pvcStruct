<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element_factory;

use pvc\struct\range_collection\range_element\RangeElementCarbon;
use pvc\struct\range_collection\range_element\RangeElementInterface;

/**
 * Class RangeElementCarbonFactory
 * @package pvc\struct\range_collection\range_element_factory
 */
class RangeElementCarbonFactory implements RangeElementFactoryInterface
{
    public function createRangeElement($min, $max) : RangeElementCarbon
    {
        return new RangeElementCarbon($min, $max);
    }
}
