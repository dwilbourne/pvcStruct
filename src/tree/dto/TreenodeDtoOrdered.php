<?php

declare(strict_types=1);

namespace pvc\struct\tree\dto;

/**
 * @template PayloadType
 * @extends TreenodeDtoUnordered<PayloadType>
 */
class TreenodeDtoOrdered extends TreenodeDtoUnordered
{
    /**
     * @var non-negative-int
     */
    public int $index;
}
