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
 * @template RangeElementType
 * @template RangeElementDataType
 * @implements RangeInterface<RangeElementType, RangeElementDataType>
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
    abstract public function getMin();

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
    abstract public function getMax();

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
     * @param RangeElementDataType $value
     * @return bool
     */
    public function isInRange($value): bool
    {
        return (($this->getMin() <= $value) && ($value <= $this->getMax()));
    }
}
