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
