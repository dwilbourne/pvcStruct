<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class DtoInvalidArrayKeyException
 */
class DtoInvalidArrayKeyException extends LogicException
{
    public function __construct(string $arrayKey, ?Throwable $prev = null)
    {
        parent::__construct($arrayKey, $prev);
    }
}
