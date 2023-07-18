<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\lists;

use pvc\interfaces\struct\lists\ListOrderedInterface;
use pvc\struct\lists\err\NonExistentKeyException;
use SplDoublyLinkedList;

/**
 * Class ListOrdered
 * @template ListElementType
 * @extends splDoublyLinkedList<ListElementType>
 * @implements ListOrderedInterface<ListElementType>
 */
class ListOrdered extends splDoublyLinkedList implements ListOrderedInterface
{
    /**
     * getElement - longhand for the array access syntax $list[key]
     * @param int $key
     * @return ListElementType|null
     */
    public function getElement(int $key)
    {
        return $this->offsetGet($key);
    }

    /**
     * getElements - return all elements in the list as an array
     * @return ListElementType[]
     */
    public function getElements(): array
    {
        $result = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $result[] = $this->current();
        }
        return $result;
    }

    /**
     * update - longhand for the array access syntax $list[$key] = $value;
     * @param int $key
     * @param $value
     */
    public function update(int $key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * delete - longhand for array access syntax offsetUnset($key]
     * @param int $key
     */
    public function delete(int $key): void
    {
        $this->offsetUnset($key);
    }

    /**
     * changeIndex - move an element from one index in the list to another
     * @param int $oldIndex
     * @param int $newIndex
     * @throws NonExistentKeyException
     * @throws NonExistentKeyException
     */
    public function changeIndex(int $oldIndex, int $newIndex): void
    {
        if (!$this->offsetExists($oldIndex)) {
            throw new NonExistentKeyException($oldIndex);
        }
        if (!$this->offsetExists($newIndex)) {
            throw new NonExistentKeyException($newIndex);
        }

        /** @var ListElementType $value */
        $value = $this->offsetGet($oldIndex);
        $this->offsetUnset($oldIndex);
        $this->add($newIndex, $value);
    }
}
