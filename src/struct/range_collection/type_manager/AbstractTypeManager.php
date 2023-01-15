<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection\type_manager;

use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class AbstractTypeManager
 * @template RangeDataType
 * @implements RangeCollectionTypeManagerInterface<RangeDataType>
 */
abstract class AbstractTypeManager implements RangeCollectionTypeManagerInterface
{
    /**
     * compareData
     * @param RangeDataType $x
     * @param RangeDataType $y
     * @return int
     */
    public function compareData($x, $y): int
    {
        return $x <=> $y;
    }

    /**
     * validateDataType
     * @param RangeDataType $x
     * @return bool
     */
    abstract public function validateDataType($x): bool;
}
