<?php

/** @noinspection PhpCSValidationInspection */

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\lists\err;

use pvc\err\ExceptionFactoryTrait;

class _ExceptionFactory
{
    use ExceptionFactoryTrait;

    public const LIBRARY_NAME = "pvcList";

    public const CODES = [
		DuplicateKeyException::class => 1001,
	    InvalidKeyException::class => 1002,
	    NonExistentKeyException::class => 1003,
    ];

    public const MESSAGES = [
	    DuplicateKeyException::class => 'duplicate key %s',
	    InvalidKeyException::class => 'Invalid key %s',
	    NonExistentKeyException::class => 'non-existent key %s',
    ];
}
