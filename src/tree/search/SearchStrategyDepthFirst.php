<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\NodeMapInterface;
use pvc\interfaces\struct\tree\search\NodeVisitableInterface;
use pvc\interfaces\struct\tree\search\VisitStatus;
use pvc\struct\tree\err\StartNodeUnsetException;

/**
 * Class SearchStrategyDepthFirst
 * @extends SearchStrategyAbstract<NodeVisitableInterface>
 */
abstract class SearchStrategyDepthFirst extends SearchStrategyAbstract
{
    use VisitationTrait;

    /**
     * @var NodeMapInterface
     */
    protected NodeMapInterface $nodeMap;

    /**
     * @param NodeMapInterface $nodeMap
     */
    public function __construct(NodeMapInterface $nodeMap)
    {
        $this->setNodeMap($nodeMap);
    }

    /**
     * getNodeMap
     * @return NodeMapInterface
     */
    public function getNodeMap(): NodeMapInterface
    {
        return $this->nodeMap;
    }

    /**
     * setNodeMap
     * @param NodeMapInterface $nodeMap
     */
    public function setNodeMap(NodeMapInterface $nodeMap): void
    {
        $this->nodeMap = $nodeMap;
    }

    /**
     * initializeVisitStatusRecurse
     * @param NodeVisitableInterface $node
     */
    protected function initializeVisitStatusRecurse(NodeVisitableInterface $node): void
    {
        $node->initializeVisitStatus();
        /** @var NodeVisitableInterface $child */
        foreach ($node->getChildrenAsArray() as $child) {
            $this->initializeVisitStatusRecurse($child);
        }
    }

    /**
     * rewind
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        parent::rewind();
        $this->nodeMap->initialize();
        $this->initializeVisitStatusRecurse($this->getStartNode());
        /**
         * currentNode gets called right after rewind.  In the normal flow, a node is visited when you call
         * the next() method, so we initialize by visiting the start node, adding it to the node map and updating its
         * visit status.
         */
        $this->nodeMap->setNode($this->getStartNode()->getNodeId(), null, $this->getStartNode());
        $this->updateVisitStatus($this->currentNode);
    }

    /**
     * allChildrenFullyVisited
     * @return bool
     * returns true if all children have been fully visited or if the node has no children
     */
    protected function allChildrenFullyVisited(): bool
    {
        /**
         * @param bool $carry
         * @param NodeVisitableInterface $childNode
         * @return bool
         */
        $callback = function (bool $carry, NodeVisitableInterface $childNode) {
            return $carry && ($childNode->getVisitStatus() == VisitStatus::FULLY_VISITED);
        };
        $childrenArray = $this->currentNode->getChildrenAsArray();
        return array_reduce($childrenArray, $callback, true);
    }

    /**
     * getNextVisitableChild
     * @return NodeVisitableInterface|null
     */
    protected function getNextVisitableChild(): ?NodeVisitableInterface
    {
        $child = null;
        foreach ($this->currentNode->getChildrenAsArray() as $child) {
            if ($child->getVisitStatus() != VisitStatus::FULLY_VISITED) {
                break;
            }
        }
        return $child;
    }

    /**
     * getParent
     * @return NodeVisitableInterface|null
     */
    protected function getParent(): ?NodeVisitableInterface
    {
        return $this->getNodeMap()->getParent($this->currentNode->getNodeId());
    }

    /**
     * move
     * @param Direction $direction
     * you can move up, move down, or you can "move nowhere", which simply updates the visitation status. So we
     * always want to call this function *before* stopping so that the visit status of the current node is updated.
     */
    protected function move(Direction $direction): void
    {
        switch ($direction) {
            case Direction::DONT_MOVE:
                $nextNode = $this->currentNode;
                $levelAdjust = 0;
                break;
            case Direction::MOVE_UP:
                $nextNode = $this->getParent();
                $levelAdjust = -1;
                break;
            case Direction::MOVE_DOWN:
                $nextNode = $this->getNextVisitableChild();
                $levelAdjust = 1;
                break;
        }

        /**
         * if the next node is not yet in the node map then add it.  $nextNode might be null....
         */
        if ($nextNode && !$this->nodeMap->getNode($nextNode->getNodeId())) {
            $this->getNodeMap()->setNode($nextNode->getNodeId(), $this->currentNode->getNodeId(), $nextNode);
        }

        /**
         * update the visit of the current node before moving (or staying)
         */
        $this->updateVisitStatus($this->currentNode);

        /**
         * move
         */
        $this->setCurrent($nextNode);

        /**
         * adjust the current level.  Current level cannot be less than 0
         */
        $this->currentLevel = max(0, $this->currentLevel + $levelAdjust);
    }


    /**
     * getMovementDirection
     * @return Direction
     *
     * returns MOVE_DOWN if we should keep iterating by recursing down through child nodes,
     * returns STOP if we should stop iterating
     * returns MOVE_UP if we should continue iterating by returning up to the parent
     */
    abstract protected function getMovementDirection(): Direction;

    /**
     * updateVisitStatus
     */
    protected function updateVisitStatus(NodeVisitableInterface $node): void
    {
        $newStatus = match ($node->getVisitStatus()) {
            VisitStatus::NEVER_VISITED => ($this->atMaxLevels() || $this->allChildrenFullyVisited()) ?
                VisitStatus::FULLY_VISITED :
                VisitStatus::PARTIALLY_VISITED,

            VisitStatus::PARTIALLY_VISITED =>
            $this->allChildrenFullyVisited() ?
                VisitStatus::FULLY_VISITED :
                VisitStatus::PARTIALLY_VISITED,

            VisitStatus::FULLY_VISITED => VisitStatus::FULLY_VISITED,
        };
        $this->currentNode->setVisitStatus($newStatus);
    }

    /**
     * endOfSearch
     * @return bool
     * end of search is when we are back at the start node and the start node is fully visited
     */
    protected function endOfSearch(): bool
    {
        return ($this->startNode->getVisitStatus() == VisitStatus::FULLY_VISITED);
    }

    /**
     * shouldStop
     * @return bool
     *
     * move until the direction says stop and the current node passes the node filter or until
     */
    protected function shouldStop(): bool
    {
        return ($this->getMovementDirection() == Direction::DONT_MOVE &&
            call_user_func($this->getNodeFilter(), $this->currentNode));
    }

    /**
     * next
     */
    public function next(): void
    {
        /**
         * you always have to move once, knowing the "moving" can simply be updating the visit status of
         * the current node from never visited to partially visited
         */
        $direction = $this->getMovementDirection();
        $priorNode = $this->currentNode;
        /**
         * update the visit status on thge prior node before or after calling shouldStop depending on whether in
         * preorder or postorder mode
         */
        $this->move($direction);

        if ($this->endOfSearch()) {
            $this->valid = false;
        } elseif (!$this->shouldStop()) {
            $this->next();
        }
    }


    protected function atMaxLevels(): bool
    {
        return ($this->currentLevel == $this->maxLevels - 1);
    }
}
