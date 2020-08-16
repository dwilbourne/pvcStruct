<?php declare(strict_types = 1);

namespace pvc\struct\lists;

use pvc\struct\lists\err\ListDuplicateKeyIndexMsg;
use pvc\struct\lists\err\ListNonExistentKeyIndexMsg;
use pvc\struct\lists\err\ListValidateOffsetException;
use pvc\struct\lists\err\ListDuplicateKeyIndexException;
use pvc\struct\lists\err\ListNonExistentKeyIndexException;
use pvc\struct\lists\err\ListValidateOffsetMsg;
use pvc\struct\lists\key_validator\ValidatorIntegerNonNegative;
use pvc\validator\base\ValidatorInterface;

/**
 * The ListUnordered class implements an "unordered list" using a php array.
 *
 * 1) Use either the add or the push method to add elements to the list.  The add method requires you to specify
 * a key, the push method auto-generates the next integer index.  push('foo') is semantically equivalent to
 * $list[] = 'foo'.
 *
 * 2) Like a standard php array, this implementation will allow you to blindly overwrite one element with
 * another when they have the same key while using ArrayAccess syntax.  For example, $list[3] = 'foo' will overwrite
 * whatever is already at index 3 in $list.  To guard against this behavior, use add / push / update methods.
 *
 */
class ListUnordered implements ListUnorderedInterface
{
    use ListIteratorTrait;

    private ValidatorInterface $keyValidator;


    public function __construct()
    {
        $this->keyValidator = new ValidatorIntegerNonNegative();
    }

    /**
     * @function isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->elements) == 0;
    }

    /**
     * returns all keys of the list as an array
     * @return array
     */

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * returns the value of an element of the list that corresponds to the given key
     * @param int $key
     * @return mixed
     * @throws ListValidateOffsetException
     */

    public function getElement(int $key)
    {
        if (!$this->keyValidator->validate($key)) {
            $this->throwListValidateOffsetException($key);
        }
        if (isset($this->elements[$key])) {
            return $this->elements[$key];
        }
        return null;
    }

    /**
     * @function getElementByValue
     * @param mixed $value
     * callback_match should be a callable that compares $value with some portion of element
     * @param callable $callback_match
     * @return bool|mixed
     *
     */
    public function getElementByValue($value, callable $callback_match)
    {
        foreach ($this->elements as $element) {
            if ($callback_match($value, $element)) {
                return $element;
            }
        }
        return null;
    }

    /**
     * @function add
     * @param int $key
     * @param mixed $value
     * @throws ListDuplicateKeyIndexException
     * @throws ListValidateOffsetException
     */
    public function add(int $key, $value): void
    {
        if (!$this->keyValidator->validate($key)) {
            $this->throwListValidateOffsetException($key);
        }
        if (isset($this->elements[$key])) {
            $this->throwListDuplicateKeyIndexException($key);
        }
        $this->elements[$key] = $value;
    }

    /**
     * @function update
     * @param int $key
     * @param mixed $value
     * @throws ListNonExistentKeyIndexException
     * @throws ListValidateOffsetException
     */
    public function update(int $key, $value): void
    {
        if (!$this->keyValidator->validate($key)) {
            $this->throwListValidateOffsetException($key);
        }
        if (!isset($this->elements[$key])) {
            $this->throwListNonExistentKeyIndexException($key);
        }
        $this->elements[$key] = $value;
    }

    /**
     * deletes an element from the list.  Returns true if successful, false if there is no element corresponding to $key
     * @param int $key
     * @return void
     * @throws ListNonExistentKeyIndexException
     * @throws ListValidateOffsetException
     */

    public function delete(int $key): void
    {
        if (!$this->keyValidator->validate($key)) {
            $this->throwListValidateOffsetException($key);
        }
        if (!isset($this->elements[$key])) {
            $this->throwListNonExistentKeyIndexException($key);
        }
        unset($this->elements[$key]);
    }

    /**
     * @function push
     * @param mixed $value
     */
    public function push($value): void
    {
        $this->elements[] = $value;
    }



    /**
     * note that for the ArrayAccess interface implementation, 'offset' means 'key' in an array.
     * It does *not* mean the numerical index of the key
     */

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * An offset to check for.
     * @return bool true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     * @throws ListValidateOffsetException
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        }
        return isset($this->elements[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * The offset to retrieve.
     * @return mixed Can return all value types.
     * @throws ListValidateOffsetException
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        }
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * The offset to assign the value to.
     * @param mixed $value <p>
     * The value to set.
     * @return void
     * @throws ListValidateOffsetException
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->elements[] = $value;
            return;
        }
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * The offset to unset.
     * @throws ListValidateOffsetException
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (!$this->keyValidator->validate($offset)) {
            $this->throwListValidateOffsetException($offset);
        }
        unset($this->elements[$offset]);
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
     * @function throwListDuplicateKeyIndexException
     * @param mixed $key
     * @throws ListDuplicateKeyIndexException
     */
    private function throwListDuplicateKeyIndexException($key) : void
    {
        $msg = new ListDuplicateKeyIndexMsg($key);
        throw new ListDuplicateKeyIndexException($msg);
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
}
