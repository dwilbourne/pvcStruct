<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

declare(strict_types = 1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class SetNodeIdException
 */
class AlreadySetNodeidException extends LogicException
{
    public function __construct(int $nodeid, Throwable $prev = null)
    {
        parent::__construct($nodeid, $prev);
    }
}
