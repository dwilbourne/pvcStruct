<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists;

/**
 * Trait ListIteratorTrait
 * @package pvc\struct\lists
 *
 * provides the properties and methods to implement the Iterator interface.
 *
 * provides the count method so that it implements the Countable interface.
 *
 */
trait ListIteratorTrait
{
    /** @phpstan-ignore-next-line  */
    protected array $elements = [];

    private int $position = 0;

    /**
     * returns all elements of the list as an array with keys
     *
     * @function getElements
     * @return array[mixed]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * Count elements of an object
     * @function count
     * @link https://php.net/manual/en/countable.count.php
     * @return int
     * @since 5.1.0
     */
    public function count() : int
    {
        return count($this->elements);
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
        $keys = array_keys($this->elements);
        return $this->elements[$keys[$this->position]];
    }

    /**
     * Move forward to next element. Any returned value is ignored.
     *
     * @function next
     * @link https://php.net/manual/en/iterator.next.php
     * @return void
     * @since 5.0.0
     */
    public function next() : void
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return int|string|null
     * @since 5.0.0
     */
    public function key()
    {
        $keys = array_keys($this->elements);
        return $this->valid() ? $keys[$this->position] : null;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool
     * @since 5.0.0
     */
    public function valid() : bool
    {
        return ($this->position < count($this->elements));
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void
     * @since 5.0.0
     */
    public function rewind() : void
    {
        $this->position = 0;
    }
}
