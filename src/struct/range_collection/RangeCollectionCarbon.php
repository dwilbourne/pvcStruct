<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection;

use pvc\struct\range_collection\range_element\RangeElementCarbon;

/**
 * Class RangeCollectionCarbon
 * @package pvc\struct\range_collection\range_element
 */
class RangeCollectionCarbon
{
    use RangeCollectionTrait;

    public function addRangeElement(RangeElementCarbon $element) : void
    {
        $this->ranges[] = $element;
    }
}
