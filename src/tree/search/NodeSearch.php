<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\search\NodeFilterInterface;
use pvc\interfaces\struct\tree\search\NodeSearchInterface;
use pvc\interfaces\struct\tree\search\NodeSearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class Search
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements NodeSearchInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class NodeSearch implements NodeSearchInterface
{
    /**
     * @var NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected NodeSearchStrategyInterface $strategy;

    /**
     * @var NodeFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected NodeFilterInterface $filter;

    /**
     * @param NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $strategy
     * @param NodeFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $filter
     */
    public function __construct(NodeSearchStrategyInterface $strategy, NodeFilterInterface $filter)
    {
        $this->setSearchStrategy($strategy);
        $this->setSearchFilter($filter);
    }

    /**
     * getStartNode
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getStartNode(): TreenodeAbstractInterface
    {
        return $this->getSearchStrategy()->getStartNode();
    }

    /**
     * setStartNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    public function setStartNode(TreenodeAbstractInterface $node): void
    {
        $this->getSearchStrategy()->setStartNode($node);
    }

    /**
     * getSearchStrategy
     * @return NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getSearchStrategy(): NodeSearchStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * setSearchStrategy
     * @param NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $strategy
     */
    public function setSearchStrategy(NodeSearchStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * getSearchFilter
     * @return NodeFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getSearchFilter(): NodeFilterInterface
    {
        return $this->filter;
    }

    /**
     * setSearchFilter
     * @param NodeFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $filter
     */
    public function setSearchFilter(NodeFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * getNodes
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
     */
    public function getNodes(): array
    {
        if (!$this->getSearchStrategy()->startNodeIsSet()) {
            throw new StartNodeUnsetException();
        }

        $nodes = [];
        foreach ($this->getSearchStrategy() as $key => $node) {
            $nodes[$key] = $node;
        }
        return $nodes;
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
