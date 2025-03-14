<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class DtoInvalidEntityGetterException
 */
class DtoInvalidEntityGetterException extends LogicException
{
    public function __construct(string $getterName, string $entityClassString, ?Throwable $prev = null)
    {
        parent::__construct($getterName, $entityClassString, $prev);
    }
}