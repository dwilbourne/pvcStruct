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
 * Class InvalidNodeIdException
 */
class InvalidNodeIdException extends Exception
{
    /**
     * InvalidNodeIdException constructor.
     * @param int|string $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'Invalid nodeid - nodeid value = %s.';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::TREENODE_INVALID_NODEID_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
