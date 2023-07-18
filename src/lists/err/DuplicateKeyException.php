<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\lists\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class ListAddException
 */
class DuplicateKeyException extends LogicException
{

    public function __construct(int $duplicateKey, Throwable $prev = null)
    {
        parent::__construct($duplicateKey, $prev);
    }
}
