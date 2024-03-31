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
use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class Search
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements SearchInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class Search implements SearchInterface
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
}
