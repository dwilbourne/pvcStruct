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
 * Class InvalidNodeDataException
 */
class InvalidNodeDataException extends InvalidArgumentException
{
    public function __construct(Throwable $previous = null)
    {
        $msgText = 'Error trying to add root / nodes to tree. Invalid node data passed to function.';
        $vars = [];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_INVALID_NODE_DATA_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
