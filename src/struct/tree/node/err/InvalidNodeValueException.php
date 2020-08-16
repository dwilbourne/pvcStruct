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
 * Class InvalidNodeValueException
 */
class InvalidNodeValueException extends Exception
{
    /**
     * InvalidNodeValueException constructor.
     * @param mixed $value
     * @param Throwable|null $previous
     */
    public function __construct($value, Throwable $previous = null)
    {
        $msgText = 'Invalid node value - value = %s.';
        $vars = [$value];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREENODE_INVALID_NODE_VALUE_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
