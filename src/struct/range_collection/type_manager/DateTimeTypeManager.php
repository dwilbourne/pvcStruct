<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\range_collection\type_manager;

use DateTime;
use pvc\interfaces\struct\range_collection\RangeCollectionTypeManagerInterface;

/**
 * Class DateTimeTypeManager
 * @template RangeDataType
 * @extends AbstractTypeManager<RangeDataType>
 * @implements RangeCollectionTypeManagerInterface<RangeDataType>
 */
class DateTimeTypeManager extends AbstractTypeManager implements RangeCollectionTypeManagerInterface
{
	/**
	 * validateDataType
	 * @param $x
	 * @return bool
	 */
    public function validateDataType($x): bool
    {
        return ($x instanceof DateTime);
    }
}
