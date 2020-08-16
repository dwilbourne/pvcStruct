<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection;

use pvc\struct\range_collection\range_element\RangeElementInteger;

/**
 * Class RangeCollectionInteger
 * @package pvc\struct\range_collection
 */
class RangeCollectionInteger
{
    use RangeCollectionTrait;

    public function addRangeElement(RangeElementInteger $element) : void
    {
        $this->ranges[] = $element;
    }
}
