<?php

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

class DtoInvalidPropertyNameException extends LogicException
{
    public function __construct(string $propertyName, ?Throwable $prev = null)
    {
        parent::__construct($propertyName, $prev);
    }
}