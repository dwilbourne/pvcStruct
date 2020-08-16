<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\lists\err;

use Exception;
use pvc\msg\ErrorExceptionMsg;
use pvc\struct\lists\err\ListDuplicateKeyIndexException;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use \Throwable;
use PHPUnit\Framework\TestCase;

class ListDuplicateKeyExceptionTest extends TestCase
{
    public function testConstruct() : void
    {
        $previous = new Exception();

        $msgText = 'test message';
        $vars = [];
        $msg = new ErrorExceptionMsg($vars, $msgText);

        $code = 0;
        $exception = new ListDuplicateKeyIndexException($msg, $code, $previous);

        self::assertTrue($exception instanceof Throwable);
        self::assertTrue($exception instanceof InvalidArgumentException);
        self::assertEquals($msgText, $exception->getMsg()->getMsgText());
        self::assertEquals(ec::LIST_DUPLICATE_KEY_INDEX_EXCEPTION, $exception->getCode());
        self::assertEquals($previous, $exception->getPrevious());
    }
}
