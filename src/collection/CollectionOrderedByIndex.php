<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionOrderedByIndexInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\err\InvalidComparatorException;
use pvc\struct\collection\err\InvalidKeyException;

/**
 * Class CollectionOrderedByIndex
 *
 *
 * @template ElementType of IndexedElementInterface
 * @extends Collection<ElementType>
 * @implements CollectionOrderedByIndexInterface<ElementType>
 *
 * this collection requires that its elements be objects and have getIndex
 * and setIndex methods to allow keeping the elements in order according
 * to the index property of each element (and to persist that order).
 *
 * The comparator in this class is immutable.
 *
 */
class CollectionOrderedByIndex extends Collection implements CollectionOrderedByIndexInterface
{
    private const int SHUFFLE_UP = 1;
    private const int SHUFFLE_DOWN = -1;

    /**
     * @param  array<non-negative-int, ElementType>  $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array);

        /**
         * setting the comparator will sort the internal array
         */
        $comparator = function (
            IndexedElementInterface $a,
            IndexedElementInterface $b
        ): int {
            return $a->getIndex() <=> $b->getIndex();
        };
        parent::setComparator($comparator);

        /**
         * Do not assume $array is properly indexed - reindex it
         */
        $i = 0;
        foreach ($this as $element) {
            $element->setIndex($i++);
        }
    }

    public function setComparator($comparator): void
    {
        /**
         * this method should not be used in this class
         */
        throw new InvalidComparatorException();
    }

    /**
     * @param  non-negative-int  $proposedIndex
     * @param  non-negative-int  $maxIndex
     *
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
     * getIndex returns the index which corresponds to $key.  This should
     * be marginally faster than the algorithm in the parent class.
     *
     *
     * @param  non-negative-int  $key
     *
     * @return non-negative-int|null
     */
    public function getIndex(int $key): ?int
    {
        if (!$this->validateExistingKey($key)) {
            throw new InvalidKeyException((string)$key);
        }
        return $this->getElement($key)->getIndex();
    }

    /**
     * setIndex allows you to move an existing element from one ordinal position in the collection to another.
     *
     * this method only does something if the $ordered flag is true
     *
     * If $newIndex is greater than the largest index in the collection, then we adjust it to be the last index in
     * the collection.  If $newIndex < 0, set it to 0.
     *
     * Rather than explicitly shuffling some indices down and some indices up, this algorithm just deletes the
     * element and adds it back with the new index.
     *
     * @param  non-negative-int  $key
     * @param  non-negative-int  $index
     */
    public function setIndex(int $key, int $index): void
    {
        $element = $this->getElement($key);

        /**
         * we know that there is at least one element in the collection because $key has been validated
         * and $element is set, but the static analyzer does not.  It thinks that count() could be 0 and therefore
         * $maxIndex could potentially be -1
         */
        $maxIndex = max(0, $this->count() - 1);

        /**
         * 'trim' the new index first
         */
        $index = $this->trimIndex($maxIndex, $index);

        $this->delete($key);
        $element->setIndex($index);

        /**
         * the add method sorts the elements array by index so we do not need to call it separately
         */
        $this->add($key, $element);
    }

    /**
     * delete
     *
     * @param  non-negative-int  $key
     *
     * note that we do not need to sort the elements array after deleting an element - it is already in order.
     */
    public function delete(int $key): void
    {
        $existingIndex = $this->getElement($key)->getIndex();
        parent::delete($key);

        $this->shuffleIndices($existingIndex, self::SHUFFLE_DOWN);
    }

    /**
     * @param  non-negative-int  $startIndex
     * @param  int<-1, 1>  $direction
     *
     * @return void
     */
    private function shuffleIndices(int $startIndex, int $direction): void
    {
        foreach ($this->iterator as $element) {
            $existingIndex = $element->getIndex();
            $newIndex = max(0, $existingIndex + $direction);

            if (
                /**
                 * make space before adding a new element at $startIndex
                 */
                ($direction == self::SHUFFLE_UP
                    && $existingIndex >= $startIndex)
                || /**
                 * remove space after deleting an element at $startIndex
                 */
                ($direction == self::SHUFFLE_DOWN
                    && $existingIndex > $startIndex)
            ) {
                $element->setIndex($newIndex);
            }
        }
    }

    /**
     * add
     *
     * @param  non-negative-int  $key
     * @param  ElementType  $element
     *
     * @throws InvalidKeyException
     *
     */
    public function add(int $key, $element): void
    {
        /**
         * 'trim' the index of the element first
         */
        $maxIndex = $this->count();
        $index = $this->trimIndex($element->getIndex(), $maxIndex);
        $element->setIndex($index);

        /**
         * shuffle the other indices before we add the element
         */
        $this->shuffleIndices($element->getIndex(), self::SHUFFLE_UP);


        /**
         * add to the collection
         */
        parent::add($key, $element);
    }

    /**
     * @param  non-negative-int  $key
     * @param $element
     *
     * @return void
     * @throws InvalidKeyException
     *
     * The code is a little DRYer if we use delete and add rather than
     * craft a separate set of code for update
     */
    public function update(int $key, $element): void
    {
        $this->delete($key);
        $this->add($key, $element);
    }
}
