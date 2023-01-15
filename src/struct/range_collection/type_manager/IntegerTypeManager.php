<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection\type_manager;

use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class IntegerTypeManager
 * @template RangeDataType
 * @extends AbstractTypeManager<RangeDataType>
 * @implements RangeCollectionTypeManagerInterface<RangeDataType>
 */
class IntegerTypeManager extends AbstractTypeManager implements RangeCollectionTypeManagerInterface
{
    /**
     * validateDataType
     * @param $x
     * @return bool
     */
    public function validateDataType($x): bool
    {
        return is_int($x);
    }
}
