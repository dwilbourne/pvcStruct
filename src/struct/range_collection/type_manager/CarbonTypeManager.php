<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection\type_manager;

use Carbon\Carbon;
use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class CarbonTypeManager
 * @template RangeDataType
 * @extends AbstractTypeManager<RangeDataType>
 * @implements RangeCollectionTypeManagerInterface<RangeDataType>
 */
class CarbonTypeManager extends AbstractTypeManager implements RangeCollectionTypeManagerInterface
{
    /**
     * validateDataType
     * @param $x
     * @return bool
     */
    public function validateDataType($x): bool
    {
        return ($x instanceof Carbon);
    }
}
