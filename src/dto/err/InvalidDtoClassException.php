<?php

declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

class InvalidDtoClassException extends LogicException
{
    public function __construct(string $badDtoClassName, ?Throwable $previous = null)
    {
        parent::__construct($badDtoClassName, $previous);
    }
}
