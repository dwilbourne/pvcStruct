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
 * Class DeleteInteriorNodeException
 */
class DeleteInteriorNodeException extends InvalidArgumentException
{
    /**
     * DeleteInteriorNodeException constructor.
     * @param int|string $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'Error trying to delete node (value = %s) - node must be a leaf ';
        $msgText .= 'in order to be eligible for deletion.';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_DELETE_INTERIOR_NODE_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
