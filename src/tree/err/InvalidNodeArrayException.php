<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\tree\err;


use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class InvalidNodeArrayException
 */
class InvalidNodeArrayException extends LogicException
{
    public function __construct(int $keyid, int $nodeid, Throwable $prev = null)
    {
        parent::__construct($keyid, $nodeid, $prev);
    }
}
