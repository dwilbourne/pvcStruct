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
use pvc\struct\collection\err\InvalidValueException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class Collection
 * @template KeyType of array-key
 * @template ElementType
 *
 * @extends IteratorIterator<mixed, ElementType, ArrayIterator<KeyType, ElementType>>
 * @implements CollectionInterface<KeyType, ElementType>
 *
 * elements in a collection cannot be null
 */
class Collection extends IteratorIterator implements CollectionInterface
{
    /**
     * @var ArrayIterator<KeyType, ElementType>
     * inner iterator
     */
    protected ArrayIterator $iterator;

    /**
     * @var ?callable(ElementType, ElementType): int $comparator ;
     */
    protected $comparator;

    /**
     * @var ValTesterInterface<KeyType>|null
     * makes sure a key is 'valid'.  Static analysis provides safety if you use it,
     * but not everyone does.  And there could be other ways in which you might
     * want to constrain the kinds of keys in the collection.
     */
    public ValTesterInterface|null $keyTester {
        get => $this->keyTester ?? null;
        set {
            $this->keyTester = $value;
        }
    }

    /**
     * @var ValTesterInterface<ElementType>|null
     * same for values
     */
    public ValTesterInterface|null $valueTester {
        get {
            return $this->valueTester ?? null;
        }
        set {
            $this->valueTester = $value;
        }
    }

    /**
     * @param ?ArrayIterator<KeyType, ElementType>  $iterator
     */
    public function __construct(?ArrayIterator $iterator = null)
    {
        if (is_null($iterator)) {
            $iterator = new ArrayIterator();
        }
        $this->iterator = $iterator;
        parent::__construct($iterator);
    }

    /**
     * initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->iterator = new ArrayIterator();
    }

    /**
     * @param ?callable(ElementType, ElementType): int  $comparator
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
     * @param  KeyType  $key
     *
     * @return non-negative-int
     */
    public function getIndex($key): int
    {
        $this->validateExistingKey($key);
        $result = $i = 0;
        foreach ($this->iterator as $n => $element) {
            if ($n === $key) {
                $result = $i;
            }
            $i++;
        }
        return $result;
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
     * @param  KeyType  $key
     *
     * @return ElementType
     */
    public function getElement($key): mixed
    {
        $this->validateExistingKey($key);

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
     * @param  KeyType  $key
     *
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    protected function validateExistingKey($key): void
    {
        if ($this->keyTester && !$this->keyTester->testValue($key)) {
            throw new InvalidKeyException((string)$key);
        }
        if (!$this->iterator->offsetExists($key)) {
            throw new NonExistentKeyException((string)$key);
        }
    }

    /**
     * @function findElementKey
     *
     * @param  ValTesterInterface<ElementType>  $valTester
     *
     * @return KeyType|null
     */
    public function findElementKey(ValTesterInterface $valTester
    ): int|string|null {
        return array_find_key($this->getElements(), [$valTester, 'testValue']);
    }

    /**
     * now implement methods explicitly defined in the interface
     */

    /**
     * @function getElements
     * @return array<KeyType, ElementType>
     */
    public function getElements(): array
    {
        return iterator_to_array($this->iterator);
    }

    /**
     *
     * @param  ValTesterInterface<ElementType>  $valTester
     *
     * @return array<KeyType>
     */
    public function findElementKeys(ValTesterInterface $valTester): array
    {
        $elements = array_filter($this->getElements(), [$valTester, 'testValue']
        );
        return array_keys($elements);
    }

    /**
     * add
     *
     * Unlike when you are dealing with a raw array, using an existing key will throw an exception instead
     * of overwriting an existing entry in the array.  Use update to be explicit about updating an entry.
     *
     * @param  KeyType  $key
     * @param  ElementType  $element
     *
     * @throws DuplicateKeyException|InvalidKeyException|InvalidValueException
     */
    public function add($key, $element): void
    {
        $this->validateNewKey($key);
        $this->validateValue($element);

        $this->iterator->offsetSet($key, $element);

        if ($this->comparator !== null) {
            $this->iterator->uasort($this->comparator);
        }
    }

    /**
     * validateNewKey ensures that the key does not exist in the collection
     *
     * @param  KeyType  $key
     */
    protected function validateNewKey($key): void
    {
        if ($this->keyTester && !$this->keyTester->testValue($key)) {
            throw new InvalidKeyException((string)$key);
        }
        if ($this->iterator->offsetExists($key)) {
            throw new DuplicateKeyException($key);
        }
    }

    /**
     * validateValue
     *
     * @param  mixed  $value
     *
     * @return void
     */
    protected function validateValue($value): void
    {
        if ($this->valueTester && !$this->valueTester->testValue($value)) {
            throw new InvalidValueException();
        }
    }

    /**
     * update assigns a new element to the entry with index $key
     *
     * @param  KeyType  $key
     * @param  ElementType  $element
     *
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function update($key, $element): void
    {
        $this->validateExistingKey($key);
        $this->validateValue($element);

        $this->iterator->offsetSet($key, $element);

        if ($this->comparator !== null) {
            $this->iterator->uasort($this->comparator);
        }
    }

    /**
     * delete removes an element from the collection.  Unlike unset, this operation throws an exception if the
     * key does not exist.
     *
     * @param  KeyType  $key
     *
     * @return void
     * @throws InvalidKeyException
     * @throws NonExistentKeyException
     */
    public function delete($key): void
    {
        $this->validateExistingKey($key);
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
