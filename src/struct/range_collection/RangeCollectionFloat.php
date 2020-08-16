<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection;

use pvc\struct\range_collection\range_element\RangeElementFloat;

/**
 * Class RangeCollectionFloat
 * @package pvc\struct\range_collection
 */
class RangeCollectionFloat implements RangeCollectionInterface
{
    use RangeCollectionTrait;

    public function addRangeElement(RangeElementFloat $element) : void
    {
        $this->ranges[] = $element;
    }
}
