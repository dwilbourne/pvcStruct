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
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class NodeTravelerTrait
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 */
trait NodeTravelerTrait
{
    /**
     * @var SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected SearchStrategyInterface $strategy;

    /**
     * @var SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected SearchFilterInterface $filter;

    /**
     * getStartNode
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getStartNode(): TreenodeAbstractInterface
    {
        return $this->getSearchStrategy()->getStartNode();
    }

    /**
     * getSearchStrategy
     * @return SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getSearchStrategy(): SearchStrategyInterface
    {
        return $this->strategy;
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
     * setSearchStrategy
     * @param SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $strategy
     */
    public function setSearchStrategy(SearchStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * setSearchFilter
     * @param SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $filter
     */
    public function setSearchFilter(SearchFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * getSearchFilter
     * @return SearchFilterInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getSearchFilter(): SearchFilterInterface
    {
        return $this->filter;
    }
}
