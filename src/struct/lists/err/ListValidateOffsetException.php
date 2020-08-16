<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists\err;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\pvc_exceptions\InvalidArrayIndexException;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use Throwable;

/**
 * Class ListValidateOffsetException
 */
class ListValidateOffsetException extends InvalidArrayIndexException
{
    /**
     * ListValidateOffsetException constructor.
     * @param ErrorExceptionMsg $msg
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(ErrorExceptionMsg $msg, int $code = 0, Throwable $previous = null)
    {
        if ($code == 0) {
            $code = ec::LIST_VALIDATE_OFFSET_EXCEPTION;
        }
        parent::__construct($msg, $code, $previous);
    }
}
