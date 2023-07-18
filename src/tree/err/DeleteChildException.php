<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types = 1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class DeleteChildException
 */
class DeleteChildException extends LogicException
{
    public function __construct(int $proposedParentNodeid, int $proposedChildNodeid, Throwable $prev = null)
    {
        parent::__construct($proposedParentNodeid, $proposedChildNodeid, $prev);
    }
}
