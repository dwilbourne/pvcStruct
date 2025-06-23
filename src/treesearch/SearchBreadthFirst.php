<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeSearchableInterface;
use pvc\struct\treesearch\err\StartNodeUnsetException;

/**
 * Class SearchStrategyBreadthFirst
 * @extends SearchAbstract<NodeSearchableInterface>
 */
class SearchBreadthFirst extends SearchAbstract
{
    /**
     * array of nodes in the "current level" of the tree
     * @var array<NodeSearchableInterface>
     */
    private array $currentLevelNodes;

    /**
     * @var int
     * index into $currentLevelNodes used to retrieve the next node
     */
    private int $currentIndex;


    /**
     * rewind
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        parent::rewind();

        $this->currentLevelNodes = [$this->getStartNode()];

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
         * If there are no nodes at the current level, set valid to false and return
         */
        if (empty($this->currentLevelNodes)) {
            $this->setCurrent(null);
            return;
        }

        /**
         * if we still have more nodes in the current level left, set the current node, increment the index
         */
        if (isset($this->currentLevelNodes[$this->currentIndex])) {
            $this->currentNode = $this->currentLevelNodes[$this->currentIndex++];
            return;
        }

        /**
         * if we are at the maximum level permitted in the search and there are no more nodes at this level to
         * process, set valid to false and return
         */

        if ($this->atMaxLevels()) {
            $this->invalidate();
        }

        /**
         * otherwise populate $currentLevelNodes with the next level of nodes
         */
        else {
            /**
             * get the nodes on the next level of the tree
             */
            $this->currentLevelNodes = $this->getNextLevelOfNodes();
            $this->setCurrentLevel(Direction::MOVE_DOWN);
            /**
             * rewind the current index and keep going
             */
            $this->currentIndex = 0;
            $this->next();
        }
    }

    /**
     * getNextLevelOfNodes
     * @return array<NodeSearchableInterface>
     */
    protected function getNextLevelOfNodes(): array
    {
        /**
         * @param NodeSearchableInterface $node
         *
         * @return array<NodeSearchableInterface>
         */
        $getChildrenCallback = function (NodeSearchableInterface $node): array {
            return $node->getChildrenArray();
        };
        $childArrays = array_map($getChildrenCallback, $this->currentLevelNodes);
        /**
         * splat operator is required to unpack the outer array
         */
        return array_merge(...$childArrays);
    }
}
