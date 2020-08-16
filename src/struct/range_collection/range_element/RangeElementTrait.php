<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\range_collection\range_element;

use pvc\err\throwable\exception\pvc_exceptions\InvalidValueException;
use pvc\err\throwable\exception\pvc_exceptions\InvalidValueMsg;

trait RangeElementTrait
{
    /**
     * @function setMin
     * @param mixed $min
     * @throws InvalidValueException
     */
    protected function setMinValue($min) : void
    {
        if ($min > $this->getMax()) {
            $additionMsg = 'Value must be less than or equal to ' . $this->frmtr->format($this->getMax());
            $msg = new InvalidValueMsg('Min value', $min, $additionMsg);
            throw new InvalidValueException($msg);
        }
        $this->min = $min;
    }

    /**
     * @function setMax
     * @param mixed $max
     * @throws InvalidValueException
     */
    protected function setMaxValue($max) : void
    {
        if ($max < $this->getMin()) {
            $additionMsg = 'Value must be greater than or equal to ' . $this->frmtr->format($this->getMin());
            $msg = new InvalidValueMsg('Min value', $max, $additionMsg);
            throw new InvalidValueException($msg);
        }
        $this->max = $max;
    }

    /**
     * bracketsValue
     * @param mixed $value
     * @return bool
     */
    protected function bracketsValue($value) : bool
    {
        return (($value >= $this->getMin()) && ($value <= $this->getMax()));
    }
}
