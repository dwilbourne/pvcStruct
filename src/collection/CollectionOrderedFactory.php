<?php

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;

/**
 * @template ElementType of IndexedElementInterface
 * @implements CollectionFactoryInterface<ElementType, CollectionOrdered>
 */
class CollectionOrderedFactory implements CollectionFactoryInterface
{
    /**
     * @param array<non-negative-int, ElementType> $elements
     * @return CollectionOrdered<ElementType>
     */
    public function makeCollection(array $elements = []): CollectionOrdered
    {
        return new CollectionOrdered($elements);
    }
}