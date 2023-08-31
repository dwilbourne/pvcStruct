<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class SearchStrategyAbstract
 * @template NodeType of TreenodeAbstractInterface
 * @implements SearchStrategyInterface<NodeType>
 */
abstract class SearchStrategyAbstract implements SearchStrategyInterface
{
    /**
     * @var NodeType
     */
    protected TreenodeAbstractInterface $startNode;

    /**
     * @var SearchFilterInterface<NodeType>
     */
    protected SearchFilterInterface $filter;

    /**
     * @param SearchFilterInterface<NodeType> $filter
     */
    public function __construct(SearchFilterInterface $filter)
    {
        $this->setSearchFilter($filter);
    }

    /**
     * setSearchFilter
     * @param SearchFilterInterface<NodeType> $filter
     */
    public function setSearchFilter(SearchFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * clearVisitCounts
     */
    public function clearVisitCounts(): void
    {
        if (!$this->getStartNode()) {
            throw new StartNodeUnsetException();
        }
        $this->clearVisitCountsRecurse($this->startNode);
    }

    /**
     * getStartNode
     * @return NodeType|null
     */
    public function getStartNode(): TreenodeAbstractInterface|null
    {
        return $this->startNode ?? null;
    }

    /**
     * setStartNode
     * @param NodeType $node
     */
    public function setStartNode(TreenodeAbstractInterface $node): void
    {
        $this->startNode = $node;
        $this->resetSearch();
    }

    /**
     * resetSearch
     */
    abstract public function resetSearch(): void;

    /**
     * clearVisitCountsRecurse
     * @param NodeType $node
     */
    protected function clearVisitCountsRecurse(TreenodeAbstractInterface $node): void
    {
        $node->clearVisitCount();
        /** @var NodeType $child */
        foreach ($node->getChildren() as $child) {
            $this->clearVisitCountsRecurse($child);
        }
    }

    /**
     * getNextNode
     * gets nodes one at a time
     * @return NodeType|null
     */
    public function getNextNode(): TreenodeAbstractInterface|null
    {
        if (!$this->getStartNode()) {
            throw new StartNodeUnsetException();
        }
        $nextNode = $this->getNextNodeProtected();
        while ($nextNode && !$this->filter->testNode($nextNode)) {
            $nextNode = $this->getNextNodeProtected();
        }
        return $nextNode;
    }

    /**
     * getNextNodeProtected
     * @return NodeType|null
     */
    abstract protected function getNextNodeProtected(): TreenodeAbstractInterface|null;

    /**
     * getNodes
     * gets all the nodes at once
     * @return array<NodeType>
     */
    public function getNodes(): array
    {
        if (!$this->getStartNode()) {
            throw new StartNodeUnsetException();
        }
        return array_filter($this->getNodesProtected(), [$this->getSearchFilter(), 'testNode']);
    }

    /**
     * getNodesProtected
     * @return array<NodeType>
     */
    abstract protected function getNodesProtected(): array;

    /**
     * getSearchFilter
     * @return SearchFilterInterface<NodeType>
     */
    public function getSearchFilter(): SearchFilterInterface
    {
        return $this->filter;
    }
}
