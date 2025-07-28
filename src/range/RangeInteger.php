<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

/**
 * @class RangeElementInteger
 * @extends Range<int>
 */
class RangeInteger extends Range
{
    /**
     * getMin
     *
     * @return int
     */
    protected function getMin(): mixed
    {
        return $this->min ?? PHP_INT_MIN;
    }

    /**
     * getMax
     *
     * @return int
     */
    protected function getMax(): mixed
    {
        return $this->max ?? PHP_INT_MAX;
    }
}
