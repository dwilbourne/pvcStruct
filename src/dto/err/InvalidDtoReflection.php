<?php
declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

class InvalidDtoReflection extends LogicException
{
    public function __construct(string $badClassString, ?Throwable $previous = null)
    {
        parent::__construct($badClassString, $previous);
    }
}