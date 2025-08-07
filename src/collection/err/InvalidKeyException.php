<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\collection\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class InvalidKeyException
 */
class InvalidKeyException extends LogicException
{
    public function __construct(string $invalidKey, ?Throwable $prev = null)
    {
        parent::__construct($invalidKey, $prev);
    }
}
