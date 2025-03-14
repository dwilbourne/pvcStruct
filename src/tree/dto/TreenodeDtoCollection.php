<?php

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use IteratorIterator;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoCollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;

/**
 * @template PayloadType
 * @extends IteratorIterator<non-negative-int, TreenodeDtoInterface<PayloadType>, CollectionInterface<TreenodeDtoInterface<PayloadType>>>
 * @implements TreenodeDtoCollectionInterface<PayloadType>
 */
class TreenodeDtoCollection extends IteratorIterator implements TreenodeDtoCollectionInterface
{
    /**
     * @param CollectionInterface<TreenodeDtoInterface<PayloadType>> $collection
     */
    public function __construct(protected CollectionInterface $collection)
    {
        parent::__construct($collection);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->collection->count();
    }
}