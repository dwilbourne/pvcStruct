<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class BadSearchLevelsException
 */
class BadSearchLevelsException extends LogicException
{
    public function __construct(int $badLevels, Throwable $prev = null)
    {
        parent::__construct($badLevels, $prev);
    }
}
