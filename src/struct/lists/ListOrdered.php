<?php declare(strict_types = 1);

namespace pvc\struct\lists;

use pvc\struct\lists\err\ListDuplicateKeyIndexMsg;
use pvc\struct\lists\err\ListDuplicateKeyIndexException;
use pvc\struct\lists\err\ListNonExistentKeyIndexMsg;
use pvc\struct\lists\err\ListNonExistentKeyIndexException;
use pvc\struct\lists\err\ListValidateOffsetMsg;
use pvc\struct\lists\err\ListValidateOffsetException;
use pvc\struct\lists\key_validator\ValidatorIntegerNonNegative;
use \SplDoublyLinkedList;

/**
 *
 * ListOrdered operates on a numerically sorted list of elements (an ordered list).  The keys in the list
 * must be sequential non-negative integers.  So, for example, when an element is deleted from the middle of the
 * list or when an element is added to the middle of the list, the keys (indices) are shuffled in order to
 * preserve the sequencing.
 *
 * Ordination is 0-based.
 *
 * The add and delete methods implement different behavior between ListOrdered and ListUnordered. In
 * ListOrdered, calling add with an index that already exists does not generate an error.  Instead, the method adds
 * the element at the requested index and all other elements whose indices are greater than or equal to the requested
 * index have their indices incremented by one (the indices are shuffled).  The reverse occurs when an
 * element is deleted.
 *
 * By contrast, ListUnordered will throw an exception if you try to add an element at an index that already exists.  It
 * presumes that if you wanted to update an element with an existing index, then you would use the update method, not
 * the add method.
 *
 * If you simply want to add an element to the end of the list, the push method is easiest.  The add method can also
 * be used by specifying any numeric index that is >= the number of elements in the list or by passing null as
 * the offset argument.
 *
 *
 */
class ListOrdered implements ListOrderedInterface
{
    /**
     * @var SplDoublyLinkedList
     */
    protected SplDoublyLinkedList $list;

    /**
     * @var ValidatorIntegerNonNegative
     */
    protected ValidatorIntegerNonNegative $keyValidator;

    /**
     * ListOrdered constructor.
     */
    public function __construct()
    {
        $this->list = new SplDoublyLinkedList();
        $this->keyValidator = new ValidatorIntegerNonNegative();
    }

    /**
     * Return the current element
     * @function current
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed
     * @since 5.0.0
     */
    public function current()
    {
        return $this->list->current();
    }

    /**
     * Move forward to next element
     * @function next
     * @link https://php.net/manual/en/iterator.next.php
     * @return void
     * @since 5.0.0
     */
    public function next() : void
    {
        $this->list->next();
    }

    /**
     * @function key
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return int
     * @since 5.0.0
     */
    public function key() : int
    {
        return $this->list->key();
    }

    /**
     * Checks if current position is valid
     * @function valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() : bool
    {
        return $this->list->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @function rewind
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() : void
    {
        $this->list->rewind();
    }

    /**
     * Whether an offset exists
     *
     * @function offsetExists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param int $offset
     * @return bool true on success or false on failure. The return value will be casted to boolean if
     * non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset) : bool
    {
        return $this->list->offsetExists($offset);
    }

    /**
     * @function offsetGet
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->list->offsetGet($offset);
    }

    /**
     * The semantics of offsetSet are different in a doubly linked list than they are for a standard array. In a doubly
     * linked list, offsetSet can only be used to update an element, not to add one.  So we tweak the offsetSet
     * method to provide semantics consistent with ListUnordered, e.g. you can add an element by specifying
     * null as the offset or any offset that is greater than or equal to the number of elements in the list.
     *
     * @function offsetSet
     * @param int|null $offset
     * @param mixed $value
     * @throws ListValidateOffsetException
     */

    public function offsetSet($offset, $value) : void
    {
        if (!is_null($offset) && $this->offsetExists($offset)) {
            $this->list->offsetSet($offset, $value);
        } else {
            $this->add($offset, $value);
        }
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param int $offset.
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) : void
    {
        $this->validateExistingOffset($offset);
        $this->list->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer. The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() : int
    {
        return count($this->list);
    }

    /**
     * @function isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->list->isEmpty();
    }

    /**
     * @function getElement
     * @param int $offset
     * @return mixed
     */
    public function getElement(int $offset)
    {
        $this->validateExistingOffset($offset);
        return $this->list[$offset];
    }

    /**
     * @function getElements
     * @return mixed[]
     */
    public function getElements(): array
    {
        $array = [];
        foreach ($this->list as $index => $value) {
            $array[$index] = $value;
        }
        return $array;
    }

    /**
     * @function getElementByValue
     * @param mixed $value
     *
     * callback_match should be a callable that compares $value with some portion of element
     * @param callable $callback_match
     *
     * @return bool|mixed
     *
     */
    public function getElementByValue($value, callable $callback_match)
    {
        foreach ($this->list as $element) {
            if ($callback_match($value, $element)) {
                return $element;
            }
        }
        return null;
    }

    /**
     *
     * this add method works differently than SplDoublyLinkedList, which will throw an error if you give it an index
     * which is greater than the length of the existing list.  This method just adds the element to the end of the list.
     *
     * It also works differently than offsetSet when provided with an offset that already exists.  This method will add
     * the value at the specified offset and shuffle all the indices to make room for the new element.  OffsetSet will
     * overwrite the existing element at the specified offset.
     *
     * @function add
     * @param int|null $offset
     * @param mixed $value
     * @throws ListValidateOffsetException ]
     */
    public function add($offset, $value): void
    {
        if (is_null($offset)) {
            $offset = count($this->list);
        }
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        }
        $offset = min($offset, count($this->list));
        $this->list->add($offset, $value);
    }

    /**
     * @function update
     * @param int $offset
     * @param mixed $value
     */
    public function update($offset, $value): void
    {
        $this->validateExistingOffset($offset);
        $this->list[$offset] = $value;
    }

    /**
     * @function delete
     * @param int $offset
     */
    public function delete($offset): void
    {
        $this->validateExistingOffset($offset);
        $this->list->offsetUnset($offset);
    }

    /**
     * @function push
     * @param mixed $value
     */
    public function push($value): void
    {
        $this->list[] = $value;
    }

    /**
     * @function changeIndex
     * @param int $oldIndex
     * @param int $newIndex
     * @throws ListDuplicateKeyIndexException
     * @throws ListNonExistentKeyIndexException
     */
    public function changeIndex(int $oldIndex, int $newIndex): void
    {
        if ($oldIndex == $newIndex) {
            $msg = new ListDuplicateKeyIndexMsg($oldIndex);
            throw new ListDuplicateKeyIndexException($msg);
        }
        if (!$this->offsetExists($oldIndex)) {
            $msg = new ListNonExistentKeyIndexMsg($oldIndex);
            throw new ListNonExistentKeyIndexException($msg);
        }
        if (!$this->offsetExists($newIndex)) {
            $msg = new ListNonExistentKeyIndexMsg($newIndex);
            throw new ListNonExistentKeyIndexException($msg);
        }
        $value = $this->list[$oldIndex];
        $this->list->offsetUnset($oldIndex);
        $this->list->add($newIndex, $value);
    }

    /**
     * @function getKeys
     * @return array
     */
    public function getKeys(): array
    {
        return range(0, count($this->list) - 1);
    }

    /**
     * @function throwListValidateOffsetException
     * @param mixed $key
     * @throws ListValidateOffsetException
     */
    private function throwListValidateOffsetException($key) : void
    {
        $msg = new ListValidateOffsetMsg($key);
        throw new ListValidateOffsetException($msg);
    }

    /**
     * @function throwListNonExistentKeyIndexException
     * @param mixed $key
     * @throws ListNonExistentKeyIndexException
     */
    private function throwListNonExistentKeyIndexException($key) : void
    {
        $msg = new ListNonExistentKeyIndexMsg($key);
        throw new ListNonExistentKeyIndexException($msg);
    }

    /**
     * @function validateExistingOffset
     * @param mixed $offset
     * @throws ListNonExistentKeyIndexException
     * @throws ListValidateOffsetException
     */
    private function validateExistingOffset($offset) : void
    {
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        }
        if (!$this->offsetExists($offset)) {
            $this->throwListNonExistentKeyIndexException($offset);
        }
    }
}
