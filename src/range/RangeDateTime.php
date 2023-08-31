<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

use DateTime;
use DateTimeImmutable;
use pvc\interfaces\struct\range\RangeDateTimeInterface;

/**
 * @class RangeElementDateTime
 * @extends Range<RangeDateTimeInterface, DateTime|DateTimeImmutable>
 */
class RangeDateTime extends Range implements RangeDateTimeInterface
{
    /**
     * @return DateTime|DateTimeImmutable
     */
    public function getMin(): DateTime|DateTimeImmutable
    {
        return $this->min;
    }

    /**
     * @return DateTime|DateTimeImmutable
     */
    public function getMax(): DateTime|DateTimeImmutable
    {
        return $this->max;
    }
}
