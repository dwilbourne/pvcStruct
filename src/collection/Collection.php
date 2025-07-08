<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use ArrayIterator;
use IteratorIterator;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class Collection
 * @template ElementType
 * @extends IteratorIterator<non-negative-int, ElementType, ArrayIterator>
 * @implements CollectionInterface<ElementType>
 *
 * elements in a collection cannot be null
 */
class Collection extends IteratorIterator implements CollectionInterface
{
    /**
     * @var ArrayIterator<int, ElementType>
     */
    protected ArrayIterator $iterator;

    /**
     * @var callable(ElementType, ElementType):int|null
     * used by getElements to return the elements in whatever sort order you choose
     */
    protected $comparator = null;

    /**
     * @param array<non-negative-int, ElementType> $elements
     * @param callable(ElementType, ElementType):int|null $comparator
     */
    public function __construct(array $elements = [], callable|null $comparator = null)
    {
        $this->iterator = new ArrayIterator($elements);
        $this->comparator = $comparator;
        parent::__construct($this->iterator);
    }

    /**
     * count
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->iterator->count();
    }

    /**
     * isEmpty returns whether the collection is empty or not
     * @return bool
     */
    public function isEmpty(): bool
    {
        return (0 == $this->count());
    }

    /**
     * validateKey encapsulates the logic that all keys must be non-negative integers
     * @param int $key
     * @return bool
     */
    protected function validateKey(int $key): bool
    {
        return $key >= 0;
    }

    /**
     * validateExistingKey ensures that the key is both valid and exists in the collection
     * @param int $key
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function validateExistingKey(int $key): bool
    {
        return $this->iterator->offsetExists($key);
    }

    /**
     * validateNewKey ensures that the key does not exist in the collection
     * @param int $key
     */
    public function validateNewKey(int $key): bool
    {
        return !$this->iterator->offsetExists($key);
    }

    /**
     * getElement
     * @param non-negative-int $key
     * @return ElementType
     */
    public function getElement(int $key): mixed
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }
        /**
         * element cannot be null
         */
        $element = $this->iterator->offsetGet($key);
        assert(!is_null($element));
        return $element;
    }

    /**
     * now implement methods explicitly defined in the interface
     */

    /**
     * @function getElements
     * @return array<ElementType>
     */
    public function getElements(): array
    {
        if ($this->comparator) {
            $this->iterator->uasort($this->comparator);
        }
        return iterator_to_array($this->iterator);
    }

    /**
     * @function getKey
     * @param ElementType $element
     * @param bool $strict
     * @return non-negative-int|false
     */
    public function getKey($element, bool $strict = true): int|false
    {
        $key = array_search($element, $this->getElements(), $strict);
        assert((is_int($key) && ($key >= 0)) || $key === false);
        return $key;
    }

    /**
     * getKeys returns all the keys in the collection where the corresponding element equals $element.
     *
     * @param ElementType $element
     * @param bool $strict
     * @return array<non-negative-int>
     */
    public function getKeys($element, bool $strict = true): array
    {
        /** @var array<non-negative-int> $keys */
        $keys = array_keys($this->getElements(), $element, $strict);
        return $keys;
    }

    /**
     * add
     *
     * Unlike when you are dealing with a raw array, using an existing key will throw an exception instead
     * of overwriting an existing entry in the array.  Use update to be explicit about updating an entry.
     *
     * @param non-negative-int $key
     * @param ElementType $element
     * @throws DuplicateKeyException
     * @throws InvalidKeyException
     */
    public function add(int $key, $element): void
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->validateNewKey($key)) {
            throw new DuplicateKeyException($key);
        }
        $this->iterator->offsetSet($key, $element);
    }

    /**
     * update assigns a new element to the entry with index $key
     *
     * @param non-negative-int $key
     * @param ElementType $element
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function update(int $key, $element): void
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }
        $this->iterator->offsetSet($key, $element);
    }

    /**
     * delete removes an element from the collection.  Unlike unset, this operation throws an exception if the
     * key does not exist.
     *
     * @param non-negative-int $key
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function delete(int $key): void
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }
        $this->iterator->offsetUnset($key);
    }

    /**
     * @param non-negative-int $proposedIndex
     * @param non-negative-int $maxIndex
     * @return non-negative-int
     *
     * there are several methods where we need to ensure the index argument
     * is between 0 and maxIndex.  It is (count - 1) when we are looking for
     * something and count when we are adding something
     */
    protected function trimIndex(int $proposedIndex, int $maxIndex): int
    {
        $proposedIndex = max($proposedIndex, 0);
        return min($proposedIndex, $maxIndex);
    }



    /**
     * @return ElementType|null
     */
    public function getFirst()
    {
        return array_values($this->getElements())[0] ?? null;
    }

    /**
     * @return ElementType|null
     */
    public function getLast()
    {
        return array_values($this->getElements())[count($this->getElements()) - 1] ?? null;
    }

    /**
     * @param  non-negative-int  $index
     *
     * @return ElementType|null
     */
    public function getNth(int $index)
    {
        $maxIndex = max(0, $this->count() - 1);
        $index = $this->trimIndex($index, $maxIndex);
        return $this->getElements()[$index] ?? null;
    }
}
