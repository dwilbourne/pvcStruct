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
 * Class InvalidParentNodeException
 */
class InvalidParentNodeException extends InvalidArgumentException
{
    /**
     * InvalidParentNodeException constructor.
     * @param int|string $parentNodeId
     * @param Throwable|null $previous
     */
    public function __construct($parentNodeId, Throwable $previous = null)
    {
        $msgText = 'Error trying to add node to tree. ';
        $msgText .= 'Parentid (value = %s) does not refer to a node that exists in the current tree.';
        $vars = [$parentNodeId];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_INVALID_PARENT_NODE_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
