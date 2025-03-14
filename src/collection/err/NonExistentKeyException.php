<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class NonExistentKeyException
 */
class NonExistentKeyException extends LogicException
{
    public function __construct(int $nonExistentKey, ?Throwable $prev = null)
    {
        parent::__construct($nonExistentKey, $prev);
    }
}
