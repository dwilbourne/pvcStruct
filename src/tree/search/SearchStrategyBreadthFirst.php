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
use pvc\interfaces\struct\tree\search\NodeSearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;

/**
 * Class SearchStrategyBreadthFirst
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @extends SearchStrategyAbstract<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 * @implements NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class SearchStrategyBreadthFirst extends SearchStrategyAbstract implements NodeSearchStrategyInterface
{
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
     * rewind
     */
    public function rewind(): void
    {
        parent::rewind();
        $this->currentLevelNodes[] = $this->getStartNode();
        /**
         * at the beginning of the iteration, the current node is returned without next() being called first. So
         * there is nothing that advances the currentIndex pointer when the start node is returned as the first
         * element in the iteration.  So really, the currentIndex should be 1, not 0
         */
        $this->currentIndex = 1;
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
            $this->setValid(false);
            return;
        }

        /**
         * if we still have more nodes in the current level left, set the current node, increment the index,
         * and add the node to the nodeDepthMap.
         */
        if (isset($this->currentLevelNodes[$this->currentIndex])) {
            $this->currentNode = $this->currentLevelNodes[$this->currentIndex++];
            $this->nodeDepthMap->setNodeDepth($this->currentNode->getNodeId(), $this->getCurrentLevel());
        } /**
         * otherwise populate $currentLevelNodes with the next level of nodes
         */
        else {
            /**
             * get the nodes on the next level of the tree
             */
            $this->currentLevelNodes = $this->getNextLevelOfNodes();
            $this->incrementCurrentLevel();
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
