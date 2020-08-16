<?php declare(strict_types = 1);

namespace pvc\struct\range_collection\range_element;

use Carbon\Carbon;
use pvc\err\throwable\exception\pvc_exceptions\InvalidValueException;
use pvc\formatter\date_time\FrmtrDateTimeAbstract;

/**
 * Class RangeElementCarbon
 */
class RangeElementCarbon implements RangeElementInterface
{
    use RangeElementTrait;

    protected FrmtrDateTimeAbstract $frmtr;
    protected Carbon $min;
    protected Carbon $max;

    public const MIN_CARBON_STRING = '-9999-01-01 00:00:00';
    public const MAX_CARBON_STRING = '9999-12-31 23:59:59';

    /**
     * @return FrmtrDateTimeAbstract
     */
    public function getFrmtr(): FrmtrDateTimeAbstract
    {
        return $this->frmtr;
    }

    /**
     * @param FrmtrDateTimeAbstract $frmtr
     */
    public function setFrmtr(FrmtrDateTimeAbstract $frmtr): void
    {
        $this->frmtr = $frmtr;
    }

    /**
     * @function setMin
     * @param Carbon $min
     * @throws InvalidValueException
     */
    public function setMin(Carbon $min) : void
    {
        $this->setMinValue($min);
    }

    /**
     * @function getMin
     * @return Carbon
     */
    public function getMin() : Carbon
    {
        return $this->min ?? new Carbon(self::MIN_CARBON_STRING);
    }

    /**
     * @function setMax
     * @param Carbon $max
     * @throws InvalidValueException
     */
    public function setMax(Carbon $max) : void
    {
        $this->setMaxValue($max);
    }

    /**
     * @function getMax
     * @return Carbon
     */
    public function getMax() : Carbon
    {
        return $this->max ?? new Carbon(self::MAX_CARBON_STRING);
    }

    public function brackets(Carbon $x) : bool
    {
        return $this->bracketsValue($x);
    }

    /**
     * RangeElementCarbon constructor.
     * @param Carbon|null $min
     * @param Carbon|null $max
     * @throws InvalidValueException
     */
    public function __construct(Carbon $min = null, Carbon $max = null)
    {
        $min = ($min ?: new Carbon(self::MIN_CARBON_STRING));
        $max = ($max ?: new Carbon(self::MAX_CARBON_STRING));

        $this->setMin($min);
        $this->setMax($max);
    }
}
