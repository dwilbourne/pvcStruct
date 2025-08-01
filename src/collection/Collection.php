<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use ArrayIterator;
use IteratorIterator;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\err\DuplicateKeyException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class Collection
 * @template ElementType
 *
 * @extends IteratorIterator<non-negative-int, ElementType, ArrayIterator>
 * @implements CollectionInterface<ElementType>
 *
 * elements in a collection cannot be null
 */
class Collection extends IteratorIterator implements CollectionInterface
{
    /**
     * @var ArrayIterator<non-negative-int, ElementType>
     */
    protected ArrayIterator $iterator;

    /**
     * @var callable(ElementType, ElementType):int|null
     * used by getElements to return the elements in whatever sort order you choose
     */
    protected $comparator = null;

    /**
     * @param  array<int, ElementType>  $elements
     * @param  callable(ElementType, ElementType):int|null  $comparator
     */
    public function __construct(
        array $elements = [],
        callable|null $comparator = null
    ) {
        /** @var array<non-negative-int, ElementType> $values */
        $values = array_values($elements);
        $this->iterator = new ArrayIteratorNonNegIntKeys($values);
        $this->comparator = $comparator;
        parent::__construct($this->iterator);
    }

    /**
     * isEmpty returns whether the collection is empty or not
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return (0 == $this->count());
    }

    /**
     * count
     *
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->iterator->count();
    }

    /**
     * getElement
     *
     * @param  non-negative-int  $key
     *
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
     * validateKey encapsulates the logic that all keys must be non-negative integers
     *
     * @param  int  $key
     *
     * @return bool
     */
    protected function validateKey(int $key): bool
    {
        return $key >= 0;
    }

    /**
     * validateExistingKey ensures that the key is both valid and exists in the collection
     *
     * @param  non-negative-int  $key
     *
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    protected function validateExistingKey(int $key): bool
    {
        return $this->iterator->offsetExists($key);
    }

    /**
     * @function findElementKey
     *
     * @param  ValTesterInterface<ElementType>  $valTester
     *
     * @return non-negative-int|null
     */
    public function findElementKey(ValTesterInterface $valTester): ?int
    {
        return array_find_key($this->getElements(), [$valTester, 'testValue']);
    }

    /**
     * now implement methods explicitly defined in the interface
     */

    /**
     * @function getElements
     * @return array<non-negative-int, ElementType>
     */
    public function getElements(): array
    {
        if ($this->comparator) {
            $this->iterator->uasort($this->comparator);
        }
        return iterator_to_array($this->iterator);
    }

    /**
     *
     * @param  ValTesterInterface<ElementType>  $valTester
     *
     * @return array<non-negative-int>
     */
    public function findElementKeys(ValTesterInterface $valTester): array
    {
        $elements = array_filter($this->getElements(), [$valTester, 'testValue']);
        return array_keys($elements);
    }

    /**
     * add
     *
     * Unlike when you are dealing with a raw array, using an existing key will throw an exception instead
     * of overwriting an existing entry in the array.  Use update to be explicit about updating an entry.
     *
     * @param  non-negative-int  $key
     * @param  ElementType  $element
     *
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
     * validateNewKey ensures that the key does not exist in the collection
     *
     * @param  non-negative-int  $key
     */
    protected function validateNewKey(int $key): bool
    {
        return !$this->iterator->offsetExists($key);
    }

    /**
     * update assigns a new element to the entry with index $key
     *
     * @param  non-negative-int  $key
     * @param  ElementType  $element
     *
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
     * @param  non-negative-int  $key
     *
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
        return array_values($this->getElements())[count($this->getElements())
        - 1] ?? null;
    }

    /**
     * @param  non-negative-int  $index
     *
     * @return ElementType|null
     */
    public function getNth(int $index)
    {
        return array_values($this->getElements())[$index] ?? null;
    }

}
