<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);


namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class InvalidTreeidException
 */
class InvalidTreeidException extends LogicException
{
    public function __construct(int $treeid, Throwable $prev = null)
    {
        parent::__construct($treeid, $prev);
    }
}
