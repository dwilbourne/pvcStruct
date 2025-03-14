<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\treesearch\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class SetMaxSearchLevelsException
 */
class SetMaxSearchLevelsException extends LogicException
{
    public function __construct(int $badLevels, ?Throwable $prev = null)
    {
        parent::__construct($badLevels, $prev);
    }
}
