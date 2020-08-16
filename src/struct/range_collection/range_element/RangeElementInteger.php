<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element;


use pvc\err\throwable\exception\pvc_exceptions\InvalidValueException;
use pvc\formatter\numeric\FrmtrInteger;

/**
 * Class RangeElementInteger
 * @package pvc\validator\base\min_max\elements
 */
class RangeElementInteger
{
    use RangeElementTrait;

    protected FrmtrInteger $frmtr;
    protected int $min;
    protected int $max;

    /**
     * @return FrmtrInteger
     */
    public function getFrmtr(): FrmtrInteger
    {
        return $this->frmtr;
    }

    /**
     * @param FrmtrInteger $frmtr
     */
    public function setFrmtr(FrmtrInteger $frmtr): void
    {
        $this->frmtr = $frmtr;
    }

    /**
     * @function setMin
     * @param int $min
     * @throws InvalidValueException
     */
    public function setMin(int $min) : void
    {
        $this->setMinValue($min);
    }

    /**
     * @function getMin
     * @return int
     */
    public function getMin() : int
    {
        return $this->min ?? PHP_INT_MIN;
    }

    /**
     * @function setMax
     * @param int $max
     * @throws InvalidValueException
     */
    public function setMax(int $max) : void
    {
        $this->setMaxValue($max);
    }

    /**
     * @function getMax
     * @return int
     */
    public function getMax() : int
    {
        return $this->max ?? PHP_INT_MAX;
    }

    public function brackets(int $x) : bool
    {
        return $this->bracketsValue($x);
    }


    public function __construct(int $min = null, int $max = null)
    {
        $min = ($min ?: PHP_INT_MIN);
        $max = ($max ?: PHP_INT_MAX);

        $this->setMin($min);
        $this->setMax($max);
    }
}
