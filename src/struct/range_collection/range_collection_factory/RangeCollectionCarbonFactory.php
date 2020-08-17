<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_collection_factory;

use pvc\struct\range_collection\RangeCollectionCarbon;

/**
 * Class RangeCollectionCarbonFactory
 * @package pvc\struct\range_collection\range_collection_factory
 */
class RangeCollectionCarbonFactory implements RangeCollectionFactoryInterface
{
    public function createRangeCollection() : RangeCollectionCarbon
    {
        return new RangeCollectionCarbon();
    }
}
