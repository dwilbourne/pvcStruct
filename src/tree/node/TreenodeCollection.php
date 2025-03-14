<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;

use IteratorIterator;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * @template PayloadType
 * @extends IteratorIterator<non-negative-int, TreenodeInterface<PayloadType>, CollectionInterface<TreenodeInterface<PayloadType>>>
 * @implements TreenodeCollectionInterface<PayloadType>
 */
class TreenodeCollection extends IteratorIterator implements TreenodeCollectionInterface
{
    /**
     * @param CollectionInterface<TreenodeInterface<PayloadType>> $collection
     */
    public function __construct(protected CollectionInterface $collection)
    {
        parent::__construct($this->collection);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @param non-negative-int $key
     * @param TreenodeInterface<PayloadType> $treeNode
     * @return void
     */
    public function add(int $key, TreenodeInterface $treeNode): void
    {
        $this->collection->add($key, $treeNode);
    }

    /**
     * @param non-negative-int $key
     * @return void
     */
    public function delete(int $key): void
    {
        $this->collection->delete($key);
    }

    /**
     * @param TreenodeInterface<PayloadType> $node
     * @return non-negative-int|false
     */
    public function getKey(mixed $node): int|false
    {
        return $this->collection->getKey($node);
    }

    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    /**
     * @return array<TreenodeInterface<PayloadType>>
     */
    public function getElements(): array
    {
        return $this->collection->getElements();
    }

    public function getIndex(int $key): int
    {
        return $this->collection->getIndex($key);
    }

    public function setIndex(int $key, int $index): void
    {
        $this->collection->setIndex($key, $index);
    }
}