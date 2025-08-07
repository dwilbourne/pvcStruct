<?php

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionOrderedByIndexFactoryInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;

/**
 * @template ElementType of IndexedElementInterface
 * @implements CollectionOrderedByIndexFactoryInterface<ElementType, CollectionOrderedByIndex>
 */
class CollectionOrderedByIndexFactory implements CollectionOrderedByIndexFactoryInterface
{
    /**
     * @param  array<non-negative-int, ElementType>  $elements
     *
     * @return CollectionOrderedByIndex<ElementType>
     */
    public function makeCollection(array $elements = []): CollectionOrderedByIndex
    {
        return new CollectionOrderedByIndex($elements);
    }
}