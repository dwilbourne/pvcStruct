<?php

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\collection\IndexedElementInterface;

/**
 * @template ElementType of IndexedElementInterface
 * @template CollectionType of CollectionOrderedInterface
 * @implements CollectionFactoryInterface<ElementType, CollectionType>
 */
class CollectionOrderedFactory implements CollectionFactoryInterface
{
    /**
     * @param  array<non-negative-int, ElementType>  $elements
     *
     * @return CollectionType<ElementType>
     */
    public function makeCollection(array $elements = []): CollectionOrderedInterface
    {
        return new CollectionOrdered($elements);
    }
}