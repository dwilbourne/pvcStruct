<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element;

use pvc\err\throwable\exception\pvc_exceptions\InvalidValueException;
use pvc\formatter\numeric\FrmtrFloat;

/**
 * Class RangeElementFloat
 * @package pvc\validator\base\min_max\elements
 */
class RangeElementFloat
{
    use RangeElementTrait;

    protected FrmtrFloat $frmtr;
    protected float $min;
    protected float $max;

    /**
     * @return FrmtrFloat
     */
    public function getFrmtr(): FrmtrFloat
    {
        return $this->frmtr;
    }

    /**
     * @param FrmtrFloat $frmtr
     */
    public function setFrmtr(FrmtrFloat $frmtr): void
    {
        $this->frmtr = $frmtr;
    }

    public function setMin(float $min) : void
    {
        $this->setMinValue($min);
    }

    /**
     * @function getMin
     * @return float
     */
    public function getMin() : float
    {
        return $this->min ?? -PHP_FLOAT_MAX;
    }

    /**
     * @function setMax
     * @param float $max
     * @throws InvalidValueException
     */
    public function setMax(float $max) : void
    {
        $this->setMaxValue($max);
    }

    /**
     * @function getMax
     * @return float
     */
    public function getMax() : float
    {
        return $this->max ?? PHP_FLOAT_MAX;
    }

    public function brackets(float $x) : bool
    {
        return $this->bracketsValue($x);
    }


    /**
     * RangeElementfloatDateTime constructor.
     * @param float|null $min
     * @param float|null $max
     * @throws InvalidValueException
     */
    public function __construct(float $min = null, float $max = null)
    {
        $min = ($min ?: -PHP_FLOAT_MAX);
        $max = ($max ?: PHP_FLOAT_MAX);

        $this->setMin($min);
        $this->setMax($max);
    }
}
