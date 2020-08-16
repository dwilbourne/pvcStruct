<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node\err;

use Exception;
use pvc\struct\tree\node\err\InvalidNodeIdException;
use \Throwable;
use PHPUnit\Framework\TestCase;

class InvalidNodeIdExceptionTest extends TestCase
{
    public function testConstruct() : void
    {
        $previousMsg = 'test message';
        $previousException = new Exception($previousMsg);
        $exception = new InvalidNodeIdException($previousException);

        static::assertTrue($exception instanceof Throwable);
        static::assertTrue($exception instanceof Exception);
    }
}
