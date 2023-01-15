<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\lists;

use pvc\interfaces\struct\lists\unordered\ListUnorderedInterface;
use pvc\struct\lists\err\_ExceptionFactory;
use pvc\struct\lists\err\DuplicateKeyException;
use pvc\struct\lists\err\InvalidKeyException;
use pvc\struct\lists\err\NonExistentKeyException;
use ArrayIterator;

/**
 * Class ListUnordered
 * @template ListElementType
 * @extends ArrayIterator<int, ListElementType>
 * @implements ListUnorderedInterface<ListElementType>
 */
class ListUnordered extends ArrayIterator implements ListUnorderedInterface
{
    /**
     * validateKey
     * @param mixed $key
     * @return bool
     */
    private function validateKey($key): bool
    {
        return (is_int($key) && ($key >= 0));
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return (0 == $this->count());
    }

    /**
     * getElement
     * @param int $key
     * @return ListElementType|null
     */
    public function getElement(int $key)
    {
        if (!$this->offsetExists($key)) {
            throw _ExceptionFactory::createException(NonExistentKeyException::class, [$key]);
        }
        return $this->offsetGet($key);
    }

    /**
     * getElements
     * @return ListElementType[]
     */
    public function getElements(): array
    {
		$result = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $result[$this->key()] = $this->current();
        }
        return $result;
    }

    /**
     * getKeys
     * @return int[]
     */
    public function getKeys(): array
    {
        $result = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $result[] = $this->key();
        }
        return $result;
    }

    /**
     * add
     * @param int $key
     * @param ListElementType $value
     */
    public function add(int $key, $value): void
    {
        if (!$this->validateKey($key)) {
            throw _ExceptionFactory::createException(InvalidKeyException::class, [$key]);
        }
        if ($this->offsetExists($key)) {
            throw _ExceptionFactory::createException(DuplicateKeyException::class, [$key]);
        }
        $this->offsetSet($key, $value);
    }

    /**
     * update
     * @param int $key
     * @param ListElementType $value
     */
    public function update(int $key, $value): void
    {
        if (!$this->validateKey($key)) {
            throw _ExceptionFactory::createException(InvalidKeyException::class, [$key]);
        }
        if (!$this->offsetExists($key)) {
            throw _ExceptionFactory::createException(NonExistentKeyException::class, [$key]);
        }
        $this->offsetSet($key, $value);
    }

    /**
     * delete
     * @param int $key
     */
    public function delete(int $key): void
    {
	    if (!$this->validateKey($key)) {
		    throw _ExceptionFactory::createException(InvalidKeyException::class, [$key]);
	    }
	    if (!$this->offsetExists($key)) {
		    throw _ExceptionFactory::createException(NonExistentKeyException::class, [$key]);
	    }
        $this->offsetUnset($key);
    }

    /**
     * push
     * @param ListElementType $value
     */
    public function push($value): void
    {
        $this->append($value);
    }
}
