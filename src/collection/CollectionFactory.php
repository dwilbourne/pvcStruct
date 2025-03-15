<?php


declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;

/**
 * @template ElementType
 * @implements CollectionFactoryInterface<ElementType>
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * @param array<non-negative-int, ElementType> $elements
     * @return CollectionInterface<ElementType>
     */
    public function makeCollection(array $elements = []): CollectionInterface
    {
        return new Collection($elements);
    }
}
