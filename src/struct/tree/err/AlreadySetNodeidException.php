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
 * Class SetNodeIdException
 */
class AlreadySetNodeidException extends InvalidArgumentException
{
    /**
     * AlreadySetNodeidException constructor.
     * @param int|null $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'Error trying to construct tree - nodeid (value = %s) already exists in the tree.';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_ALREADY_SET_NODEID_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
