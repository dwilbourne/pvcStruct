<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class collectionAbstract
 * @template PayloadType of HasPayloadInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements CollectionAbstractInterface<PayloadType, CollectionType>
 */
abstract class CollectionAbstract implements CollectionAbstractInterface
{
    /**
     * @var array<non-negative-int, PayloadType> $elements ;
     */
    protected array $elements = [];

    /**
     * @var non-negative-int $position
     */
    private int $position;

    /**
     * implementation of ArrayAccess.  This implementation restricts keys to be non-negative integers.
     */

    /**
     * current
     * @return PayloadType
     */
    public function current(): mixed
    {
        return $this->elements[$this->position];
    }

    /**
     * next
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * key
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * implement iterator functions
     */

    /**
     * valid
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->elements[$this->position]);
    }

    /**
     * rewind
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * count
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * isEmpty returns whether the collection is empty or not
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * getElement
     * @param non-negative-int $key
     * @return PayloadType
     */
    public function getElement(int $key): mixed
    {
        $this->validateExistingKey($key);
        return $this->elements[$key];
    }

    /**
     * implement Countable interface
     */

    /**
     * validateExistingKey ensures that the key is both valid and exists in the collection
     * @param int $key
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    protected function validateExistingKey(int $key): void
    {
        $this->validateKey($key);
        if (!isset($this->elements[$key])) {
            throw new NonExistentKeyException($key);
        }
    }

    /**
     * now implement methods explicitly defined in the interface
     */

    /**
     * validateKey encapsulates the logic that all keys must be non-negative integers
     * @param int $key
     * @return void
     * @throws InvalidKeyException
     */
    protected function validateKey(int $key): void
    {
        if ($key < 0) {
            throw new InvalidKeyException($key);
        }
    }

    /**
     * @function getElements
     * @return array<PayloadType>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @function getKey
     * @param PayloadType $element
     * @param bool $strict
     * @return non-negative-int|null
     */
    public function getKey($element, bool $strict = true): ?int
    {
        $key = array_search($element, $this->elements, $strict);
        return ($key === false) ? null : $key;
    }

    /**
     * add puts an element into the collection
     *
     * the add method has different behaviors between ordered collections and unordered collections.  For ordered
     * collections, it exhibits as an "insert" behavior: puts an element into a certain position in this list and
     * reindexes other elements in the collection as necessary to keep the keys in sequentially ascending order.  For
     * an unordered list, the key supplied to the add method must be a new key which does not yet appear in the
     * collection in order to avoid overwriting an existing element (e.g. that would be an update operation).
     */

    /**
     * getKeys returns all the keys in the collection where the corresponding element equals $element.
     *
     * The implementation is a little painful.  The array_keys verb does the job, but the return type does not match
     * the restriction in this class that keys are non-negative integers.
     *
     * @param PayloadType $element
     * @param bool $strict
     * @return array<non-negative-int>
     */
    public function getKeys($element, bool $strict = true): array
    {
        /** @var array<non-negative-int> $keys */
        $keys = array_keys($this->elements, $element, $strict);
        return $keys;
    }

    /**
     * add
     *
     * This is the default behavior (e.g. unordered collection).  It is overridden in CollectionOrdered
     *
     * @param non-negative-int $key
     * @param PayloadType $payload
     * @throws DuplicateKeyException
     * @throws InvalidKeyException
     */
    public function add(int $key, $payload): void
    {
        $this->validateNewKey($key);
        $this->elements[$key] = $payload;
    }

    /**
     * delete removes an element from the collection
     *
     * the delete method has different behaviors between ordered and unordered collections.  For ordered collections,
     * the element is removed and then other elements are reindexed as necessary to maintain the keys in sequentially
     * ascending order.  For unordered lists, no reindexing is necessary.
     */

    /**
     * validateNewKey ensures that the key is both valid and does not exist in the collection
     * @param int $key
     * @throws DuplicateKeyException
     * @throws InvalidKeyException
     */
    protected function validateNewKey(int $key): void
    {
        $this->validateKey($key);
        if (isset($this->elements[$key])) {
            throw new DuplicateKeyException($key);
        }
    }

    /**
     * update assigns a new element to the entry with index $key
     * @param non-negative-int $key
     * @param PayloadType $payload
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function update(int $key, $payload): void
    {
        $this->validateExistingKey($key);
        $this->elements[$key] = $payload;
    }

    /**
     * delete removes an element from the collection
     *
     * this is the default implementation which is for an unordered collection.  it is overridden in CollectionOrdered
     *
     * @param non-negative-int $key
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function delete(int $key): void
    {
        $this->validateExistingKey($key);
        unset($this->elements[$key]);
    }

    /**
     * push adds an element to the end of the collection
     * @param PayloadType $payload
     */
    public function push(mixed $payload): void
    {
        $this->elements[] = $payload;
    }
}
