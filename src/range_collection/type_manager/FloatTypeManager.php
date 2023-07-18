<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection\type_manager;

use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class FloatTypeManager
 * @template RangeDataType
 * @extends AbstractTypeManager<RangeDataType>
 * @implements RangeCollectionTypeManagerInterface<RangeDataType>
 */
class FloatTypeManager extends AbstractTypeManager implements RangeCollectionTypeManagerInterface
{
    /**
     * validateDataType
     * @param $x
     * @return bool
     * allow integers to pass as well as floats
     */
    public function validateDataType($x): bool
    {
        return (is_float($x) || is_int($x));
    }
}
