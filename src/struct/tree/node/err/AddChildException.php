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
 * Class AddChildException
 */
class AddChildException extends Exception
{
    public function __construct(Throwable $previous = null)
    {
        $msgText = 'addChild error: unable to add child.';
        $vars = [];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREENODE_ADD_CHILD_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
