<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_collection_factory;


use pvc\struct\range_collection\RangeCollectionInteger;

/**
 * Class RangeCollectionIntegerFactory
 * @package pvc\struct\range_collection\range_collection_factory
 */
class RangeCollectionIntegerFactory implements RangeCollectionFactoryInterface
{
    public function createRangeCollection() : RangeCollectionInteger
    {
        return new RangeCollectionInteger();
    }
}