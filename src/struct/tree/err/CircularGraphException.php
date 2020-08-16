<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\err;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\Exception;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use Throwable;

/**
 * Class CircularGraphException
 */
class CircularGraphException extends Exception
{
    /**
     * CircularGraphException constructor.
     * @param int|string $nodeid
     * @param Throwable|null $previous
     */
    public function __construct($nodeid, Throwable $previous = null)
    {
        $msgText = 'circular graph error: this node cannot be its own ancestor (nodeid = %s).';
        $vars = [$nodeid];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = ec::CIRCULAR_GRAPH_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
