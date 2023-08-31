<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\struct\tree\err\BadSearchLevelsException;

/**
 * Class SearchStrategyBreadthFirst
 * @template NodeType of TreenodeAbstractInterface
 * @extends SearchStrategyAbstract<NodeType>
 * @implements SearchStrategyInterface<NodeType>
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
     * @var NodeType[]
     * array of nodes in the "current level" of the tree
     */
    private array $currentLevelNodes;
    /**
     * @var int
     * index into $currentLevelNodes used to retrieve the next node
     */
    private int $currentIndex;

    /**
     * @param NodeType $startNode
     * @param int $maxLevels
     * @throws BadSearchLevelsException
     */

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
     * resetSearch
     */
    public function resetSearch(): void
    {
        $this->clearVisitCounts();
        /** @var array<NodeType> $currentLevelNodes */
        $currentLevelNodes = [$this->getStartNode()];
        $this->currentLevelNodes = $currentLevelNodes;
        $this->currentIndex = 0;
    }

    /**
     * getNextNode
     * @return NodeType|null
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
             * resetSearch max levels and current index and go get another node
             */
            $this->maxLevels--;
            $this->currentIndex = 0;
            return $this->getNextNode();
        }
    }

    /**
     * getNextLevelOfNodes
     * @return array<NodeType>
     */
    protected function getNextLevelOfNodes(): array
    {
        $getChildrenCallback = function (TreenodeAbstractInterface $node): array {
            return $node->getChildren()->getElements();
        };
        /** @var array<NodeType> $result */
        $result = call_user_func_array('array_merge', array_map($getChildrenCallback, $this->currentLevelNodes));
        return $result;
    }


    /**
     * getNodesProtected
     * @return array<NodeType>
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
     * @param array<NodeType> $nodes
     * @return array<NodeType>
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
