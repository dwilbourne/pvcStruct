<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
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
 * @extends SearchStrategyAbstract<PayloadType, NodeType, TreeType, CollectionType>
 * @implements SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType>
 */
class SearchStrategyBreadthFirst extends SearchStrategyAbstract implements SearchStrategyInterface
{

    /**
     * @var int
     *
     * maximum depth to which we are allowed to traverse the tree.  The interpretation is number of levels *below*
     * the start node, not inclusive of the start node.
     */
    protected int $maxLevels = PHP_INT_MAX;

    /**
     * @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
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
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        $this->currentLevelNodes = [$this->getStartNode()];
        $this->currentNode = $this->getStartNode();
        /**
         * at the beginning of the iteration, the current node is returned without next() being called first. So there
         * is nothing that advances the currentIndex pointer when the startnode is returned as the first element in the
         * iteration.  So really, the currentIndex should be 1, not 0
         */
        $this->currentIndex = 1;
    }

    /**
     * current
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null
     */
    protected function getNextNodeProtected(): TreenodeAbstractInterface|null
    {
        /**
         * max levels is decremented by one each time we move to the next level of nodes in the tree.  Because the
         * interpretation of max levels is the number of levels *below* the start node, test that max levels is less
         * than 0, not <= 0
         */
        if ($this->maxLevels < 0) {
            return null;
        }

        /**
         * return if there are no nodes left to process
         */
        if (empty($this->currentLevelNodes)) {
            return null;
        }

        /**
         * if we still have more nodes in the current level left, return the next one.
         */
        if (isset($this->currentLevelNodes[$this->currentIndex])) {
            $this->currentIndex++;
            return $this->currentLevelNodes[$this->currentIndex - 1];
        } /**
         * otherwise populate $currentLevelNodes with the next level of nodes
         */
        else {
            /**
             * get the nodes on the next level of the tree
             */
            $this->currentLevelNodes = $this->getNextLevelOfNodes();
            /**
             * decrement max levels, rewind the current index and go get another node
             */
            $this->maxLevels--;
            $this->currentIndex = 0;
            return $this->getNextNodeProtected();
        }
    }

    /**
     * getNextLevelOfNodes
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    protected function getNextLevelOfNodes(): array
    {
        $getChildrenCallback = function (TreenodeAbstractInterface $node): array {
            return $node->getChildren()->getElements();
        };
        /** @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>> $result */
        $result = call_user_func_array('array_merge', array_map($getChildrenCallback, $this->currentLevelNodes));
        return $result;
    }


    /**
     * getNodesProtected
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    protected function getNodesProtected(): array
    {
        /** @var array<NodeType> $startNodeArray */
        $startNodeArray = [$this->getStartNode()];
        /** @var array<NodeType> $result */
        $result = $this->getNodesRecurse($startNodeArray);
        return $result;
    }

    /**
     * getNodesRecurse
     * @param array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>> $nodes
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    protected function getNodesRecurse(array $nodes): array
    {
        if ($this->maxLevels-- === 0) {
            return $nodes;
        }

        $this->currentLevelNodes = $this->getNextLevelOfNodes();

        /**
         * if there are children, recurse on them and merge into the current result set.  If there are no remaining
         * children, just return the current result set.
         */
        if (!empty($this->currentLevelNodes)) {
            return array_merge($nodes, $this->getNodesRecurse($this->currentLevelNodes));
        } else {
            return $nodes;
        }
    }
}
