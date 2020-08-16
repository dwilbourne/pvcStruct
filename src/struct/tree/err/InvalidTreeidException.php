<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\err;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\err\throwable\exception\stock_rebrands\Exception;
use pvc\msg\ErrorExceptionMsg;
use Throwable;

/**
 * Class InvalidTreeidException
 */
class InvalidTreeidException extends Exception
{
    /**
     * InvalidTreeidException constructor.
     * @param int|null $nodeid
     * @param mixed $treeIdOfNode
     * @param mixed $treeidOfTree
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, $treeIdOfNode, $treeidOfTree, Throwable $previous = null)
    {
        $msgText = 'Error - node with nodeid = %s has a treeid = %s and that does not match the the ';
        $msgText .= 'containing tree whose id is %s.';
        $vars = [$nodeid, $treeIdOfNode, $treeidOfTree];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_NODE_NOT_IN_TREE_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
