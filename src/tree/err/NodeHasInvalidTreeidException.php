<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\tree\err;


use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class NodeHasInvalidTreeidException
 */
class NodeHasInvalidTreeidException extends LogicException
{
    public function __construct(int $nodeid, int $treeidOfNode, int $treeidOfTree, Throwable $prev = null)
    {
        parent::__construct($nodeid, $treeidOfNode, $treeidOfTree, $prev);
    }
}
