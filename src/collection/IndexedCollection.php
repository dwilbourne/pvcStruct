<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use ArrayIterator;
use pvc\interfaces\struct\collection\IndexedCollectionInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\err\ComparatorException;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\InvalidValueException;
use Throwable;

/**
 * Class CollectionOrderedByIndex
 *
 * @template KeyType of array-key
 * @template ElementType of IndexedElementInterface
 * @extends Collection<KeyType, ElementType>
 * @implements IndexedCollectionInterface<KeyType, ElementType>
 *
 * this collection requires that its elements be objects and have getIndex
 * and setIndex methods to allow keeping the elements in order according
 * to the index property of each element (and to persist that order).
 *
 * The comparator in this class is immutable.
 *
 */
class IndexedCollection extends Collection implements IndexedCollectionInterface
{
    private const int SHUFFLE_UP = 1;
    private const int SHUFFLE_DOWN = -1;

    /**
     * @param  ?ArrayIterator<KeyType, ElementType>  $iterator
     */
    public function __construct(?ArrayIterator $iterator = null)
    {
        parent::__construct($iterator);

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
        throw new ComparatorException();
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

    public function getIndex($key): int
    {
        $this->validateExistingKey($key);
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
     * @param  KeyType  $key
     * @param  non-negative-int  $index
     */
    public function setIndex($key, int $index): void
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
        $this->add($element, $key);
    }

    /**
     * delete
     *
     * @param  KeyType  $key
     *
     * note that we do not need to sort the elements array after deleting an element - it is already in order.
     */
    public function delete($key): void
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
     * @param  KeyType  $key
     * @param  ElementType  $element
     * @param non-negative-int $index
     *
     * @throws InvalidValueException|InvalidKeyException
     *
     * if the element does not already have its index property set, this method
     * will add the element to the end of the collection and set the element's
     * index to the appropriate value.
     */
    public function add($element, $key): void
    {
        /**
         * ensure the index property of the element is set.
         */
        try {
            $index = $element->getIndex();
        } catch (Throwable $e) {
            $index = $this->count();
        }

        /**
         * 'trim' the index of the element so that it is not larger than
         * the number of elements currently in the collection.
         */
        $index = $this->trimIndex($index, $this->count());
        $element->setIndex($index);

        /**
         * shuffle the other indices before we add the element
         */
        $this->shuffleIndices($element->getIndex(), self::SHUFFLE_UP);


        /**
         * add to the collection
         */
        parent::add($element, $key);
    }

    /**
     * @param  KeyType  $key
     * @param $element
     *
     * @return void
     * @throws InvalidKeyException
     *
     * The code is a little DRYer if we use delete and add rather than
     * craft a separate set of code for update
     */
    public function update($key, $element): void
    {
        $this->delete($key);
        $this->add($element, $key);
    }
}
