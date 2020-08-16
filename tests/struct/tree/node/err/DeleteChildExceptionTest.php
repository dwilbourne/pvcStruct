<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node\err;

use Exception;
use pvc\struct\tree\node\err\DeleteChildException;
use \Throwable;
use PHPUnit\Framework\TestCase;

class DeleteChildExceptionTest extends TestCase
{
    public function testConstruct() : void
    {
        $previous = new Exception();
        $nodeid = 4;
        $parentNodeid = 0;
        $exception = new DeleteChildException($nodeid, $parentNodeid, $previous);

        static::assertTrue($exception instanceof Throwable);
        static::assertTrue($exception instanceof Exception);
    }
}
