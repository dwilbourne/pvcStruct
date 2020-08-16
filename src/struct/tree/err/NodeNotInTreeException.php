<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\err;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use Throwable;

/**
 * Class NodeNotInTreeException
 */
class NodeNotInTreeException extends InvalidArgumentException
{
    /**
     * NodeNotInTreeException constructor.
     * @param int|null $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'Error - there is no node in this tree with nodeid = %s.';
        $node = (string) $nodeid ?: 'null';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_NODE_NOT_IN_TREE_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
