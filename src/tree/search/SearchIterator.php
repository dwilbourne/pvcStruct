<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use Iterator;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchIteratorInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class SearchIterator
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements SearchIteratorInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 * @phpcs:ignore
 * @implements Iterator<int, TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
 */
class SearchIterator implements SearchIteratorInterface, Iterator
{
    /**
     * @use NodeTravelerTrait<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    use NodeTravelerTrait;

    /**
     * @param SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $strategy
     * @param SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $filter
     */
    public function __construct(SearchStrategyInterface $strategy, SearchFilterInterface $filter)
    {
        $this->setSearchStrategy($strategy);
        $this->setSearchFilter($filter);
    }

    /**
     * key
     * @return int|null
     */
    public function key(): ?int
    {
        return $this->getSearchStrategy()->key();
    }

    /**
     * next
     * advances current to the next node if possible
     */
    public function next(): void
    {
        /**
         * because of the filtering, it is possible that there are no more nodes in the tree that will pass through
         * the filter, even if there are more nodes in the tree.
         */
        $this->getSearchStrategy()->next();
        while ($this->current() && !$this->filter->testNode($this->current())) {
            $this->getSearchStrategy()->next();
        }
    }

    /**
     * current
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    public function current(): TreenodeAbstractInterface|null
    {
        return $this->getSearchStrategy()->current();
    }

    /**
     * rewind
     */
    public function rewind(): void
    {
        $this->getSearchStrategy()->rewind();
    }

    /**
     * valid
     * @return bool
     */
    public function valid(): bool
    {
        return $this->getSearchStrategy()->valid();
    }
}
