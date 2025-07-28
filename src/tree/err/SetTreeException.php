<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\tree\err;


use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class SetTreeIdException
 */
class SetTreeException extends LogicException
{
    public function __construct(int $nodeId, ?Throwable $previous = null)
    {
        parent::__construct($nodeId, $previous);
    }
}
