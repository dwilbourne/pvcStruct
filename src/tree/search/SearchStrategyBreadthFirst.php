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
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class SearchStrategyBreadthFirst
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class SearchStrategyBreadthFirst implements SearchStrategyInterface
{
    /**
     * @use SearchStrategyTrait<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    use SearchStrategyTrait;

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
    private int $currentLevel = 0;

    /**
     * @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
     *
     * array of nodes in the "current level" of the tree
     */
    private array $currentLevelNodes;

    /**
     * @var int
     * index into $currentLevelNodes used to retrieve the next node
     */
    private int $currentIndex;

    /**
     * @param NodeDepthMapInterface $nodeDepthMap
     */
    public function __construct(NodeDepthMapInterface $nodeDepthMap)
    {
        $this->setNodeDepthMap($nodeDepthMap);
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
     * rewind
     */
    public function rewind(): void
    {
        if (!$this->startNodeIsSet()) {
            throw new StartNodeUnsetException();
        }
        $this->valid = true;
        $this->currentLevel = 0;
        $this->nodeDepthMap->initialize();
        $this->currentLevelNodes[] = $this->getStartNode();
        $this->currentNode = $this->getStartNode();
        /**
         * at the beginning of the iteration, the current node is returned without next() being called first. So
         * there is nothing that advances the currentIndex pointer when the start node is returned as the first
         * element in the iteration.  So really, the currentIndex should be 1, not 0
         */
        $this->currentIndex = 1;
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
        return ($this->currentLevel == $this->maxLevels);
    }

    /**
     * next
     */
    public function next(): void
    {
        /**
         * If we have exceeded the max levels or there are no nodes left to process, set valid to false
         * and return
         */
        if (($this->exceededMaxLevels()) || empty($this->currentLevelNodes)) {
            $this->valid = false;
            return;
        }

        /**
         * if we still have more nodes in the current level left, set the current node, increment the index,
         * and add the node to the nodeDepthMap.
         */
        if (isset($this->currentLevelNodes[$this->currentIndex])) {
            $this->currentNode = $this->currentLevelNodes[$this->currentIndex++];
            $this->nodeDepthMap->setNodeDepth($this->currentNode->getNodeId(), $this->currentLevel);
        } /**
         * otherwise populate $currentLevelNodes with the next level of nodes
         */
        else {
            /**
             * get the nodes on the next level of the tree
             */
            $this->currentLevelNodes = $this->getNextLevelOfNodes();
            $this->currentLevel++;
            /**
             * rewind the current index and keep going
             */
            $this->currentIndex = 0;
            $this->next();
        }
    }

    /**
     * getNextLevelOfNodes
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
     */
    protected function getNextLevelOfNodes(): array
    {
        $getChildrenCallback = function (TreenodeAbstractInterface $node): array {
            return $node->getChildren()->getElements();
        };
        /** @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>> $result */
        $result = call_user_func_array('array_merge', array_map($getChildrenCallback, $this->currentLevelNodes));
        return $result;
    }
}
