<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection;


/**
 * Class RangeCollection
 */
trait RangeCollectionTrait
{
    /**
     * @var array[RangeElement]
     */
    protected array $ranges = [];

    /**
     * @function getRangeElements
     * @return array[RangeElements]
     */
    public function getRangeElements() : array
    {
        return $this->ranges;
    }
}
