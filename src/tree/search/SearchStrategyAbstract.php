<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class SearchStrategyAbstract
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType>
 */
abstract class SearchStrategyAbstract implements SearchStrategyInterface
{
    /**
     * @var SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    protected SearchFilterInterface $filter;

    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    protected TreenodeAbstractInterface $startNode;

    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null
     */
    protected TreenodeAbstractInterface|null $currentNode;

    /**
     * @param SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType> $filter
     */
    public function __construct(SearchFilterInterface $filter)
    {
        $this->setSearchFilter($filter);
    }

    /**
     * getStartNode
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null
     */
    public function getStartNode(): TreenodeAbstractInterface|null
    {
        return $this->startNode ?? null;
    }

    /**
     * setStartNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $node
     */
    public function setStartNode(TreenodeAbstractInterface $node): void
    {
        $this->startNode = $node;
        $this->rewind();
    }

    /**
     * setSearchFilter
     * @param SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType> $filter
     */
    public function setSearchFilter(SearchFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * getSearchFilter
     * @return SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    public function getSearchFilter(): SearchFilterInterface
    {
        return $this->filter;
    }

    /**
     * getNodes
     * gets all the nodes at once
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    public function getNodes(): array
    {
        return array_filter($this->getNodesProtected(), [$this->getSearchFilter(), 'testNode']);
    }

    /**
     * getNodesProtected
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    abstract protected function getNodesProtected(): array;

    /**
     * current
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null
     */
    public function current(): TreenodeAbstractInterface|null
    {
        return $this->currentNode ?? null;
    }

    /**
     * key
     * @return int|null
     */
    public function key(): ?int
    {
        return $this->currentNode?->getNodeId();
    }

    /**
     * next
     * advances current to the next node if possible
     */
    public function next(): void
    {
        /**
         * because of the filtering, it is possible that there are no more nodes in the tree that will pass through
         * the filter, even if there are more nodes in the tree.  This implementation sets $this->current to be null
         * when there are no more nodes to traverse.
         */
        $this->currentNode = $this->getNextNodeProtected();
        while ($this->currentNode && !$this->filter->testNode($this->currentNode)) {
            $this->currentNode = $this->getNextNodeProtected();
        }
    }

    /**
     * rewind is implemented in the child classes
     */

    /**
     * valid
     * @return bool
     */
    public function valid(): bool
    {
        return (!is_null($this->currentNode));
    }

    /**
     * getNextNodeProtected
     * @return NodeType|null
     */
    abstract protected function getNextNodeProtected(): TreenodeAbstractInterface|null;
}
