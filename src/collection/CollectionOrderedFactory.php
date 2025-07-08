<?php

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;

/**
 * @template ElementType of IndexedElementInterface
 * @implements CollectionFactoryInterface<ElementType, CollectionOrderedInterface>
 */
class CollectionOrderedFactory implements CollectionFactoryInterface
{
    /**
     * @param array<non-negative-int, ElementType> $elements
     * @return CollectionOrderedInterface<ElementType>
     */
    public function makeCollection(array $elements = [])
    {
        return new CollectionOrdered($elements);
    }
}