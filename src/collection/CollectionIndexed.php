<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;
use pvc\struct\collection\err\InvalidKeyException;
use pvc\struct\collection\err\NonExistentKeyException;

/**
 * Class CollectionIndexed
 *
 * elements in an ordered collection must have a getter called getIndex.  That value is used to order the elements
 * prior to returning them via get elements.  You can get the index of an element via its key.  You can also set the
 * index of an element via its key and all the other elements in the collection will have their indices updated
 * accordingly.
 *
 * @template ElementType of IndexedElementInterface
 * @extends Collection<ElementType>
 * @implements CollectionInterface<ElementType>
 */
class CollectionIndexed extends Collection implements CollectionInterface
{
    private const int SHUFFLE_UP = 1;
    private const int SHUFFLE_DOWN = -1;

    /**
     * @var callable(ElementType, ElementType):int
     * used by getElements to return the elements in order of index
     */
    protected $comparator;

    /**
     * @param array<non-negative-int, ElementType> $array
     */
    public function __construct(array $array = [])
    {
        $comparator = function($a, $b) {
            /**
             * @var ElementType $a
             * @var ElementType $b
             */
            return $a->getIndex() <=> $b->getIndex();
        };
        parent::__construct($array, $comparator);

        /**
         * do not assume that the indices of the elements being imported are continuously ascending starting at 0.
         */
        $this->iterator->uasort($this->comparator);

        /**
         * renumber the indices
         */
        $i = 0;
        foreach ($this->iterator as $element) {
            $element->setIndex($i++);
        }
    }

    /**
     * setIndex allows you to move an existing element from one ordinal position in the collection to another.
     *
     * If $newIndex is greater than the largest index in the collection, then we adjust it to be the last index in
     * the collection.  If $newIndex < 0, set it to 0.
     *
     * Rather than explicitly shuffling some indices down and some indices up, this algorithm just deletes the
     * element and adds it back with the new index.
     *
     * @param non-negative-int $key
     * @param non-negative-int $newIndex
     */
    public function setIndex(int $key, int $newIndex): void
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
        $newNewIndex = $this->trimIndex($maxIndex, $newIndex);

        $this->delete($key);
        $element->setIndex($newIndex);

        /**
         * the add method sorts the elements array by index so we do not need to call it separately
         */
        $this->add($key, $element);
    }

    /**
     * @param non-negative-int $proposedIndex
     * @param non-negative-int $maxIndex
     * @return non-negative-int
     *
     * the add method and the setIndex method both need to ensure that the proposed index of the element is >= 0.
     * If we are adding an element, the max index value is count(). If we are setting the index of an existing
     * element to a new value, the max index value is count() - 1.
     */
    private function trimIndex(int $proposedIndex, int $maxIndex): int
    {
        $proposedIndex = max($proposedIndex, 0);
        return min($proposedIndex, $maxIndex);
    }

    /**
     * delete
     * @param non-negative-int $key
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
     * @param non-negative-int $startIndex
     * @param int<-1, 1> $direction
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
                ($direction == self::SHUFFLE_UP && $existingIndex >= $startIndex) ||
                /**
                 * remove space after deleting an element at $startIndex
                 */
                ($direction == self::SHUFFLE_DOWN && $existingIndex > $startIndex)
            ) {
                $element->setIndex($newIndex);
            }
        }
    }

    /**
     * add
     * @param non-negative-int $key
     * @param ElementType $element
     * @throws InvalidKeyException
     *
     * in order to keep the elements array in the proper order, we need to sort it by index so that iteration
     * happens in the proper order
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
         * add and then sort the collection
         */
        parent::add($key, $element);
        $this->iterator->uasort([$this, 'compareIndices']);
    }

    /**
     * @param non-negative-int $key
     * @param $element
     * @return void
     * @throws InvalidKeyException
     *
     * This method will reorder the collection if the index of $element has changed from the prior value of
     * the index of the element with the same key
     */
    public function update(int $key, $element): void
    {
        $oldIndex = $this->getElement($key)->getIndex();
        $newIndex = $element->getIndex();
        $element->setIndex($oldIndex);
        parent::update($key, $element);
        $this->setIndex($key, $newIndex);
    }

    /**
     * @param ElementType $a
     * @param ElementType $b
     * @return int
     */
    protected function compareIndices(mixed $a, mixed $b): int
    {
        return $a->getIndex() <=> $b->getIndex();
    }

    /**
     * getIndex returns the index which corresponds to $key
     *
     * @param non-negative-int $key
     * @return non-negative-int
     */
    public function getIndex(int $key): int
    {
        if (!$this->validateKey($key)) {
            throw new InvalidKeyException($key);
        }
        if (!$this->validateExistingKey($key)) {
            throw new NonExistentKeyException($key);
        }
        $element = $this->getElement($key);
        assert(!is_null($element));
        return $element->getIndex();
    }
}
