<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\NodeSearchableInterface;
use pvc\struct\tree\err\StartNodeUnsetException;

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
     * exceededMaxLevels
     * @return bool
     * as an example, max levels of 2 means the first level (containing the start node) is at level 0 and the level
     * below that is on level 1.  So if the current level goes to level 2 then we have exceeded the max-levels
     * threshold.
     */
    protected function exceededMaxLevels(): bool
    {
        return ($this->currentLevel > $this->maxLevels - 1);
    }

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
         * If we have exceeded the max levels or there are no nodes left to process, set valid to false
         * and return
         */
        if (($this->exceededMaxLevels()) || empty($this->currentLevelNodes)) {
            $this->valid = false;
            return;
        }

        /**
         * if we still have more nodes in the current level left, set the current node, increment the index
         */
        if (isset($this->currentLevelNodes[$this->currentIndex])) {
            $this->currentNode = $this->currentLevelNodes[$this->currentIndex++];
            if (!call_user_func($this->getNodeFilter(), $this->currentNode)) {
                $this->next();
            }
        }
        /**
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
     * @return array<NodeSearchableInterface>
     * you cannot type hint the callback.  Not sure why the compiler is
     * complaining when you type hint the argument as NodeSearchableInterface, but it kicks out a type error when
     * you test it with a real object other than a mock of NnodeSearchableInterface
     */
    protected function getNextLevelOfNodes(): array
    {
        $getChildrenCallback = function ($node): array {
            /** @var NodeSearchableInterface $node */
            $childNodes = $node->getChildrenAsArray();
            foreach ($childNodes as $childNode) {
                $this->nodeMap->setNode($childNode->getNodeId(), $node->getNodeId(), $childNode);
            }
            return $childNodes;
        };
        $childArrays = array_map($getChildrenCallback, $this->currentLevelNodes);
        /**
         * note the splat operator is required to unpack the outer array
         */
        return array_merge(...$childArrays);
    }
}
