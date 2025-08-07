<?php

namespace pvc\struct\collection;

use ArrayIterator;
use pvc\struct\collection\err\InvalidKeyException;

/**
 * @template ElementType
 * @extends ArrayIterator<non-negative-int, ElementType>
 */
class ArrayIteratorNonNegIntKeys extends ArrayIterator
{
    /**
     * @param  array<non-negative-int, ElementType>  $array
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $key => $element) {
            $this->validateKey($key);
        }
        parent::__construct($array);
    }

    /**
     * validateKey encapsulates the logic that all keys must be non-negative integers
     *
     * @param  array-key  $key
     *
     * @return void
     */
    protected function validateKey(int|string $key)
    {
        if (!is_int($key) || $key < 0) {
            throw new InvalidKeyException($key);
        }
    }

    /**
     * @param  array-key  $key
     * @param  mixed  $value
     *
     * @return void
     * @throws InvalidKeyException
     * even though this library is written with phpstan as a tool, this puts
     * a hard exception in  place if the key is invalid
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->validateKey($key);
        parent::offsetSet($key, $value);
    }
}