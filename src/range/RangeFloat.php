<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

use pvc\interfaces\struct\range\RangeFloatInterface;

/**
 * @class RangeElementFloat
 * @extends Range<RangeFloatInterface, float>
 */
class RangeFloat extends Range implements RangeFloatInterface
{
    /**
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }
}
