<?php

declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

class PropertMapInvalidKeyException extends LogicException
{
    public function __construct(string $key, ?Throwable $prev = null)
    {
        parent::__construct($key, $prev);
    }
}