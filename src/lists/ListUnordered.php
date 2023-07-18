<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\lists;

use ArrayIterator;
use pvc\interfaces\struct\lists\ListUnorderedInterface;
use pvc\struct\lists\err\DuplicateKeyException;
use pvc\struct\lists\err\InvalidKeyException;
use pvc\struct\lists\err\NonExistentKeyException;

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
     * @throws NonExistentKeyException
     */
    public function getElement(int $key)
    {
        if (!$this->offsetExists($key)) {
            throw new NonExistentKeyException($key);
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
     * @throws DuplicateKeyException
     * @throws InvalidKeyException
     */
    public function add(int $key, $value): void
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if ($this->offsetExists($key)) {
            throw new DuplicateKeyException($key);
        }
        $this->offsetSet($key, $value);
    }

    /**
     * update
     * @param int $key
     * @param ListElementType $value
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function update(int $key, $value): void
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->offsetExists($key)) {
            throw new NonExistentKeyException($key);
        }
        $this->offsetSet($key, $value);
    }

    /**
     * delete
     * @param int $key
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function delete(int $key): void
    {
	    if (!$this->validateKey($key)) {
		    throw new InvalidKeyException($key);
	    }
	    if (!$this->offsetExists($key)) {
		    throw new NonExistentKeyException($key);
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
