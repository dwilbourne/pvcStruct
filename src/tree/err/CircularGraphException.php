<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class CircularGraphException
 */
class CircularGraphException extends LogicException
{
    public function __construct(int $nodeid, Throwable $prev = null)
    {
        parent::__construct($nodeid, $prev);
    }
}
