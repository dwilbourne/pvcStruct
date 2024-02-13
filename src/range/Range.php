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
 * The class provides methods for creating a range, getting a range as an array, getting the min and the max of the
 * range.  This is all done in an object-oriented way which is overkill for the interface, but makes it far easier to
 * extend the behavior of a range to do other things.
 *
 * @template RangeElementDataType
 * @implements RangeInterface<RangeElementDataType>
 */
abstract class Range implements RangeInterface
{
    /**
     * @var RangeElementDataType
     */
    protected $min;

    /**
     * @var RangeElementDataType
     */
    protected $max;

    /**
     * @param RangeElementDataType $x
     * @param RangeElementDataType $y
     */
    public function __construct($x, $y)
    {
        $min = ($x < $y) ? $x : $y;
        $max = ($x < $y) ? $y : $x;
        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * getRange
     * @return array<RangeElementDataType>
     */
    public function getRange(): array
    {
        return [$this->getMin(), $this->getMax()];
    }

    /**
     * getMin
     * @return RangeElementDataType
     */
    abstract public function getMin(): mixed;

    /**
     * setMin
     * @param RangeElementDataType $min
     */
    public function setMin($min): void
    {
        $this->min = $min;
    }

    /**
     * getMax
     * @return RangeElementDataType
     */
    abstract public function getMax(): mixed;

    /**
     * setMax
     * @param RangeElementDataType $max
     */
    public function setMax($max): void
    {
        $this->max = $max;
    }

    /**
     * isInRange
     * @param RangeElementDataType $x
     * @return bool
     */
    public function isInRange($x): bool
    {
        return (($this->getMin() <= $x) && ($x <= $this->getMax()));
    }
}
