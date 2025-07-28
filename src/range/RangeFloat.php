<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

/**
 * @class RangeElementFloat
 * @extends Range<float>
 */
class RangeFloat extends Range
{
    /**
     * getMin
     *
     * @return float
     */
    protected function getMin(): mixed
    {
        return $this->min ?? PHP_FLOAT_MIN;
    }

    /**
     * getMax
     *
     * @return float
     */
    protected function getMax(): mixed
    {
        return $this->max ?? PHP_FLOAT_MAX;
    }
}
