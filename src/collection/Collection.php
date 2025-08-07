<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

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
 * @extends IteratorIterator<non-negative-int, ElementType, ArrayIteratorNonNegIntKeys>
 * @implements CollectionInterface<ElementType>
 *
 * elements in a collection cannot be null
 */
class Collection extends IteratorIterator implements CollectionInterface
{
    /**
     * @var ArrayIteratorNonNegIntKeys<ElementType>
     */
    protected ArrayIteratorNonNegIntKeys $iterator;

    /**
     * @var ?callable(ElementType, ElementType): int $comparator;
     */
    protected $comparator;


    /**
     * @param  array<non-negative-int, ElementType>  $elements
     */
    public function __construct(
        array $elements = [],
    ) {
        $this->setInnerIterator($elements);
        parent::__construct($this->iterator);
    }

    /**
     * @param  array<non-negative-int, ElementType>  $elements
     *
     * @return void
     * this method is also used to initialize the collection by
     * calling it with no parameters
     */
    protected function setInnerIterator(array $elements = []): void
    {
        $this->iterator = new ArrayIteratorNonNegIntKeys($elements);
    }

    /**
     * @param ?callable(ElementType, ElementType): int $comparator
     *
     * @return void
     */
    public function setComparator($comparator): void
    {
        $this->comparator = $comparator;
        if ($this->comparator !== null) {
            $this->iterator->uasort($this->comparator);
        }
    }

    /**
     * @param  non-negative-int  $key
     *
     * @return non-negative-int|null
     */
    public function getIndex(int $key): ?int
    {
            $i = 0;
            foreach ($this->iterator as $n => $element) {
                if ($n === $key) return $i;
                $i++;
            }
            return null;
    }

    public function initialize(): void
    {
        $this->setInnerIterator();
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
        if (!$this->validateNewKey($key)) {
            throw new DuplicateKeyException($key);
        }
        $this->iterator->offsetSet($key, $element);

        if ($this->comparator !== null) {
            $this->iterator->uasort($this->comparator);
        }
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
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }

        $this->iterator->offsetSet($key, $element);

        if ($this->comparator !== null) {
            $this->iterator->uasort($this->comparator);
        }

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
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }
        $this->iterator->offsetUnset($key);
    }

    /**
     * @return ElementType|null
     */
    public function getFirst(): mixed
    {
        return array_values($this->getElements())[0] ?? null;
    }

    /**
     * @return ElementType|null
     */
    public function getLast(): mixed
    {
        return array_values($this->getElements())[count($this->getElements())
        - 1] ?? null;
    }

    /**
     * @param  non-negative-int  $index
     *
     * @return ElementType|null
     */
    public function getNth(int $index): mixed
    {
        return array_values($this->getElements())[$index] ?? null;
    }

}
