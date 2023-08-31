<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

use pvc\interfaces\struct\range\RangeIntegerInterface;

/**
 * @class RangeElementInteger
 * @extends Range<RangeIntegerInterface, int>
 */
class RangeInteger extends Range implements RangeIntegerInterface
{

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }


    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }
}
