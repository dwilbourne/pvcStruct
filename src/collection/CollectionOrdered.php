<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\collection\err\InvalidKeyException;

/**
 * Class CollectionOrdered
 *
 * Keys of the collection are also the ordinal indices.  When collection elements are added, subtracted, moved,
 * the keys get shuffled to make sure they are all sequential integers.
 *
 * @template PayloadType of HasPayloadInterface
 * @extends CollectionAbstract<PayloadType, CollectionOrderedInterface>
 * @implements CollectionOrderedInterface<PayloadType>
 */
class CollectionOrdered extends CollectionAbstract implements CollectionOrderedInterface
{
    /**
     * getIndex returns the index which corresponds to $key
     *
     * In this implementation this method is purely reflexive.  But we can imagine a different implementation where
     * keys and indices are not the same thing.
     *
     * @param non-negative-int $key
     * @return int
     */
    public function getIndex(int $key): int
    {
        return $key;
    }

    /**
     * add
     * @param non-negative-int $key
     * @param PayloadType $payload
     * @throws InvalidKeyException
     */
    public function add(int $key, $payload): void
    {
        $this->validateKey($key);

        /**
         * push the payload onto the end of the collection
         */
        $this->push($payload);

        /**
         * get existing key of what we just pushed on the end of the collection and set its index to the correct
         * position.  It is confusing because $this->lastIndex() is where the element actually is (which is its key)
         * and $key is where we want it to be, so it looks backwards.
         */
        /**
         * type checker does not know that lastIndex must be >= 0 because the collection is not empty
         * @var non-negative-int $lastIndex
         */
        $lastIndex = $this->lastIndex();
        $this->setIndex($lastIndex, $key);
    }

    /**
     * lastIndex
     * returning -1 means there are no elements in the collection
     * @return int
     */
    public function lastIndex(): int
    {
        return (count($this) - 1);
    }

    /**
     * setIndex allows you to move an element from one position in the list to another.
     *
     * If $newIndex is greater than the largest index in the collection, then we adjust it to be the last index in
     * the collection.
     *
     * @param non-negative-int $key
     * @param non-negative-int $newIndex
     */
    public function setIndex(int $key, int $newIndex): void
    {
        /**
         * make sure $key is valid.  As a by-product, we know that if $key is valid then the collection has at least
         * 1 element in it.
         */
        $this->validateExistingKey($key);

        /**
         * adjust $newIndex if necessary
         * @var non-negative-int $newIndex
         */
        $newIndex = min($newIndex, $this->lastIndex());

        /**
         * save the element first
         */
        $value = $this->elements[$key];

        /**
         * if $key < $newIndex then shuffle keys from $key + 1 to $newIndex downwards by one and then set the saved
         * element at key = $newIndex. Otherwise, shuffle all indices upwards from $newIndex to $key - 1 upwards by one
         * and then set saved element at $newIndex.  The argument order for shuffleUp and shuffleDown is "start" and
         * "finish".  To shuffle down, you start with the smaller number and work your way up.  To shuffle up, you
         * start with the larger number and work your way down.
         */
        if ($key < $newIndex) {
            $this->shuffleDown($key, $newIndex);
        } else {
            $this->shuffleUp($key, $newIndex);
        }

        /**
         * put the payload into the new index
         */
        $this->elements[$newIndex] = $value;
    }

    /**
     * shuffleDown
     * @param non-negative-int $start
     * @param non-negative-int $finish
     */
    protected function shuffleDown(int $start, int $finish): void
    {
        /**
         * $start is less than finish, work our way up the indices.
         */
        for ($i = $start; $i < $finish; $i++) {
            $this->elements[$i] = $this->elements[$i + 1];
        }
    }

    /**
     * shuffleUp
     * @param non-negative-int $start
     * @param non-negative-int $finish
     */
    protected function shuffleUp(int $start, int $finish): void
    {
        /**
         * $start is greater than or equal to finish, work our way down the indices.
         */
        for ($i = $start; $i > $finish; $i--) {
            $this->elements[$i] = $this->elements[$i - 1];
        }
    }

    /**
     * delete
     * @param non-negative-int $key
     */
    public function delete(int $key): void
    {
        $this->validateExistingKey($key);

        /**
         * phpstan does not know that if $key is valid then the last index method returns a non-negative-int
         */
        $lastIndex = $this->lastIndex();
        assert($lastIndex >= 0);

        /**
         * shuffle down all the elements from the end of the collection
         */
        $this->shuffleDown($key, $lastIndex);
        array_pop($this->elements);
    }
}
