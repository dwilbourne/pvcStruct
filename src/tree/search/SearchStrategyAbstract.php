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
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class SearchStrategyAbstract
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 */
abstract class SearchStrategyAbstract
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
     * @var int
     *
     * maximum depth to which we are allowed to traverse the tree.
     */
    protected int $maxLevels = PHP_INT_MAX;

    /**
     * @var non-negative-int
     * the start node is on level 0
     */
    private int $currentLevel;

    /**
     * @var bool
     * flag indicating whether we can go to the next node or not.  Initialize to false.  It becomes true after
     * the rewind method is called.
     */
    protected bool $valid = false;

    /**
     * @param NodeDepthMapInterface $nodeDepthMap
     */
    public function __construct(NodeDepthMapInterface $nodeDepthMap)
    {
        $this->setNodeDepthMap($nodeDepthMap);
    }

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
     * getMaxLevels
     * @return int
     */
    public function getMaxLevels(): int
    {
        return $this->maxLevels;
    }

    /**
     * setMaxLevels
     * @param int $maxLevels
     * @throws BadSearchLevelsException
     */
    public function setMaxLevels(int $maxLevels): void
    {
        if ($maxLevels < 1) {
            throw new BadSearchLevelsException($maxLevels);
        } else {
            $this->maxLevels = $maxLevels;
        }
    }

    /**
     * getCurrentLevel
     * @return non-negative-int
     */
    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    /**
     * setCurrentLevel
     * @param non-negative-int $currentLevel
     */
    public function setCurrentLevel(int $currentLevel): void
    {
        $this->currentLevel = $currentLevel;
    }

    /**
     * incrementCurrentLevel
     */
    public function incrementCurrentLevel(): void
    {
        $this->currentLevel++;
    }

    /**
     * decrementCurrentLevel
     */
    public function decrementCurrentLevel(): void
    {
        assert($this->currentLevel > 0);
        $this->currentLevel--;
    }

    /**
     * exceededMaxLevels
     * @return bool
     * as an example, max levels of 2 means the first level (containing the start node) is at level 0 and the level
     * below that is on level 1.  So if the current level goes to level 2 then we have exceeded the max-levels
     * threshold.
     */
    protected function exceededMaxLevels(): bool
    {
        return ($this->currentLevel >= $this->maxLevels);
    }

    /**
     * atMaxLevel
     * @return bool
     */
    public function atMaxLevel(): bool
    {
        return ($this->currentLevel == ($this->maxLevels - 1));
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

    /**
     * setValid
     * @param bool $valid
     */
    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * rewind
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        if (!$this->startNodeIsSet()) {
            throw new StartNodeUnsetException();
        }
        $this->setValid(true);
        $this->setCurrentLevel(0);
        $this->nodeDepthMap->initialize();
        $this->setCurrentNode($this->getStartNode());
        $this->getNodeDepthMap()->setNodeDepth($this->getStartNode()->getNodeId(), 0);
    }

    abstract public function next(): void;
}
