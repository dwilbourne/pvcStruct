<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element_factory;

use pvc\struct\range_collection\range_element\RangeElementFloat;

/**
 * Class RangeElementFloatFactory
 * @package pvc\struct\range_collection\range_element_factory
 */
class RangeElementFloatFactory implements RangeElementFactoryInterface
{
    public function createRangeElement($min, $max) : RangeElementFloat
    {
        return new RangeElementFloat($min, $max);
    }
}
