<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\err;

use pvc\struct\tree\err\_ExceptionFactory;
use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\AddChildException;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetParentException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteChildException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\UnsetValueException;

class _ExceptionFactoryTest extends TestCase
{
    protected array $params = [
	    AddChildException::class => [],
	    AlreadySetNodeidException::class => [4],
	    AlreadySetParentException::class => [],
	    AlreadySetRootException::class => [],
	    CircularGraphException::class => [7],
	    DeleteChildException::class => [5, 9],
	    DeleteInteriorNodeException::class => [3],
	    InvalidNodeIdException::class => [99],
	    InvalidNodeValueException::class => [],
	    InvalidParentNodeException::class => [77],
	    InvalidTreeidException::class => [17,2,3],
	    NodeNotInTreeException::class => [2, 16],
	    UnsetValueException::class => [5],
    ];

    /**
     * testExceptions
     * @covers \pvc\struct\tree\err\_ExceptionFactory::createException
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
