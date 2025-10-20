<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class InvalidParentNodeException
 */
class InvalidParentNodeIdException extends LogicException
{
    public function __construct(string $parentid, ?Throwable $prev = null)
    {
        parent::__construct($parentid, $prev);
    }
}
