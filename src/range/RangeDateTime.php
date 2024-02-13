<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\range;

use DateTime;
use DateTimeImmutable;

/**
 * @class RangeElementDateTime
 * @extends Range<DateTime|DateTimeImmutable>
 */
class RangeDateTime extends Range
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
