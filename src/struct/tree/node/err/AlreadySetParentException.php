<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\node\err;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\Exception;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use Throwable;

/**
 * Class AlreadySetParentException
 */
class AlreadySetParentException extends Exception
{
    /**
     * AlreadySetParentException constructor.
     * @param int|string $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'addChild error: parent attribute of node argument (nodeid = %s) is already set.';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREENODE_ALREADY_SET_PARENT_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
