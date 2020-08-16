<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists\err;

use pvc\msg\ErrorExceptionMsg;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use Throwable;

/**
 * Class ListAddException
 */
class ListDuplicateKeyIndexException extends InvalidArgumentException
{
    public function __construct(ErrorExceptionMsg $msg, int $code = 0, Throwable $previous = null)
    {
        if ($code == 0) {
            $code = ec::LIST_DUPLICATE_KEY_INDEX_EXCEPTION;
        }
        parent::__construct($msg, $code, $previous);
    }
}
