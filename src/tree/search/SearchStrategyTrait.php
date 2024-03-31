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
use pvc\interfaces\struct\tree\search\NodeDepthMapInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class SearchStrategyAbstract
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
trait SearchStrategyTrait
{
    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected TreenodeAbstractInterface $startNode;

    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected TreenodeAbstractInterface $currentNode;

    /**
     * @var NodeDepthMapInterface
     */
    protected NodeDepthMapInterface $nodeDepthMap;

    /**
     * @var bool
     * flag indicating whether we can go to the next node or not.  Initialize to false.  It becomes true after
     * the rewind method is called.
     */
    protected bool $valid = false;

    /**
     * getStartNode
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getStartNode(): TreenodeAbstractInterface
    {
        return $this->startNode;
    }

    /**
     * setStartNode
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $startNode
     */
    public function setStartNode(TreenodeAbstractInterface $startNode): void
    {
        $this->startNode = $startNode;
    }

    /**
     * startNodeIsSet
     * @return bool
     */
    public function startNodeIsSet(): bool
    {
        return !is_null($this->startNode ?? null);
    }

    /**
     * getNodeDepthMap
     * @return NodeDepthMapInterface
     */
    public function getNodeDepthMap(): NodeDepthMapInterface
    {
        return $this->nodeDepthMap;
    }

    /**
     * setNodeDepthMap
     * @param NodeDepthMapInterface $nodeDepthMap
     */
    public function setNodeDepthMap(NodeDepthMapInterface $nodeDepthMap): void
    {
        $this->nodeDepthMap = $nodeDepthMap;
    }

    /**
     * current
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function current(): TreenodeAbstractInterface
    {
        return $this->getCurrentNode();
    }

    /**
     * getCurrentNode
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getCurrentNode(): TreenodeAbstractInterface
    {
        return $this->currentNode;
    }

    /**
     * setCurrentNode
     * @phpcs:ignore
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $currentNode
     */
    public function setCurrentNode(TreenodeAbstractInterface $currentNode): void
    {
        $this->currentNode = $currentNode;
    }

    /**
     * key
     * @return int
     */
    public function key(): int
    {
        return $this->currentNode->getNodeId();
    }

    /**
     * valid
     * @return bool
     */
    public function valid(): bool
    {
        return $this->valid;
    }
}
