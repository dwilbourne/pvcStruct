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
class AlreadySetRootException extends InvalidArgumentException
{
    public function __construct(Throwable $previous = null)
    {
        $msgText = 'Error trying to set root of tree - root of tree is already set.';
        $vars = [];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREE_ALREADY_SET_ROOT_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
