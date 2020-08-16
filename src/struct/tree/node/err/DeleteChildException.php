<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\node\err;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\OutOfRangeException;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use Throwable;

/**
 * Class DeleteChildException
 */
class DeleteChildException extends OutOfRangeException
{
    /**
     * DeleteChildException constructor.
     * @param int|string $nodeid
     * @param int|string $parentNodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, $parentNodeid, Throwable $previous = null)
    {
        $msgText = 'deleteChild error:  Node with id = %s is not a child of node with id = %s.';
        $vars = [$nodeid, $parentNodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREENODE_DELETE_CHILD_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
