<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists\err;

use Exception;
use pvc\msg\ErrorExceptionMsg;
use pvc\struct\lists\err\ListNonExistentKeyIndexException;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use \Throwable;
use PHPUnit\Framework\TestCase;

class ListNonExistentKeyExceptionTest extends TestCase
{
    public function testConstruct() : void
    {
        $msgText = 'test message';
        $vars = [];
        $msg = new ErrorExceptionMsg($vars, $msgText);
        $code = 0;
        $previous = new Exception();
        $exception = new ListNonExistentKeyIndexException($msg, $code, $previous);

        static::assertTrue($exception instanceof Throwable);
        static::assertTrue($exception instanceof InvalidArgumentException);
    }
}
