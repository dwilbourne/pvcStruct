<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_collection_factory;

use pvc\struct\range_collection\RangeCollectionFloat;

/**
 * Class RangeCollectionFloatFactory
 * @package pvc\struct\range_collection\range_collection_factory
 */
class RangeCollectionFloatFactory implements RangeCollectionFactoryInterface
{
    public function createRangeCollection() : RangeCollectionFloat
    {
        return new RangeCollectionFloat();
    }
}
