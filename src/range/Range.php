<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\range;

use pvc\interfaces\struct\range\RangeInterface;

/**
 * @class Range
 *
 * The class provides methods for creating a range, getting a range as an array, checking if a payload is in the range.
 *
 * @template RangeElementDataType
 * @implements RangeInterface<RangeElementDataType>
 */
abstract class Range implements RangeInterface
{
    /**
     * @var RangeElementDataType|null
     */
    protected $min;

    /**
     * @var RangeElementDataType|null
     */
    protected $max;

    /**
     * @param  RangeElementDataType  $min
     * @param  RangeElementDataType  $max
     */
    public function setRange($min, $max): void
    {
        $this->min = ($min < $max) ? $min : $max;
        $this->max = ($min < $max) ? $max : $min;
    }

    /**
     * getRange
     *
     * @return array<RangeElementDataType>
     */
    public function getRange(): array
    {
        return [$this->getMin(), $this->getMax()];
    }

    /**
     * getMin
     *
     * @return RangeElementDataType
     */
    abstract protected function getMin(): mixed;

    /**
     * getMax
     *
     * @return RangeElementDataType
     */
    abstract protected function getMax(): mixed;

    /**
     * isInRange
     *
     * @param  RangeElementDataType  $x
     *
     * @return bool
     */
    public function isInRange($x): bool
    {
        return (($this->getMin() <= $x) && ($x <= $this->getMax()));
    }
}
