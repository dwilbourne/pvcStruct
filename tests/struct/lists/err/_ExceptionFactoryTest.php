<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\lists\err;

use pvc\struct\lists\err\_ExceptionFactory;
use pvc\struct\lists\err\DuplicateKeyException;
use pvc\struct\lists\err\InvalidKeyException;
use pvc\struct\lists\err\NonExistentKeyException;
use PHPUnit\Framework\TestCase;

class _ExceptionFactoryTest extends TestCase
{
    protected array $params = [
	    DuplicateKeyException::class => [3],
	    InvalidKeyException::class => ['foo'],
	    NonExistentKeyException::class => [5],
    ];

    /**
     * testExceptions
     * @covers \pvc\struct\lists\err\_ExceptionFactory::createException
     */
    public function testExceptions(): void
    {
        foreach ($this->params as $classString => $paramArray) {
            $exception = _ExceptionFactory::createException($classString, $paramArray);
            self::assertTrue(0 < $exception->getCode());
            self::assertNotEmpty($exception->getMessage());
        }
    }
}
