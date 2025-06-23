<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeMapInterface;
use pvc\interfaces\struct\treesearch\NodeVisitableInterface;
use pvc\interfaces\struct\treesearch\VisitStatus;
use pvc\struct\treesearch\err\StartNodeUnsetException;

/**
 * Class SearchDepthFirst
 * @extends SearchAbstract<NodeVisitableInterface>
 */
abstract class SearchDepthFirst extends SearchAbstract
{
    /**
     * @param NodeMapInterface $nodeMap
     */
    public function __construct(protected NodeMapInterface $nodeMap)
    {
    }

    /**
     * rewind
     * @throws StartNodeUnsetException
     */
    public function rewind(): void
    {
        parent::rewind();

        /**
         * set the visit status of all the nodes to NEVER_VISITED
         */
        $this->initializeVisitStatusRecurse($this->getStartNode());

        /**
         * initialize the node map
         */
        $this->nodeMap->initialize($this->getStartNode());
    }

    /**
     * initializeVisitStatusRecurse
     *
     * @param  NodeVisitableInterface  $node
     */
    protected function initializeVisitStatusRecurse(NodeVisitableInterface $node): void
    {
        $node->initializeVisitStatus();
        foreach ($node->getChildrenArray() as $child) {
            $this->initializeVisitStatusRecurse($child);
        }
    }

    /**
     * next
     * rewind fails if there is no start node.  If start node is set then
     * you can always move, knowing the "moving" can simply be updating the visit status of
     * the current node from never visited to partially visited to fully visited
     */
    public function next(): void
    {
        $direction = $this->getMovementDirection();
        $this->move($direction);

        if ($this->endOfSearch()) {
            $this->invalidate();
            $direction = Direction::DONT_MOVE;
        }

        /**
         * move until the direction says stop
         */
        if ($direction != Direction::DONT_MOVE) {
            $this->next();
        }
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
     * move
     * @return void
     *
     * you can move up, move down, or you can "move nowhere", which simply updates the visitation status.  The
     * getDirection method is sensitive to the max levels property and will not allow a move 'below' max levels
     * or 'above' startnode.
     *
     * returns true if we should stop moving through the tree and return current.
     * returns false if we should keep moving through the tree
     */
    protected function move(Direction $direction): void
    {
        /**
         * get the next node (could be null at the end of a search)
         */
        /** @var NodeVisitableInterface $nextNode */
        $nextNode = $this->getNextNode($direction);

        if (is_null($nextNode)) {
            $this->invalidate();
        }
        else {
            /**
             * move
             */
            $this->setCurrent($nextNode);

            /**
             * adjust the current level
             */
            $this->setCurrentLevel($direction);
        }
    }

    protected function getNextNode(Direction $direction): ?NodeVisitableInterface
    {
        switch ($direction) {
            case Direction::DONT_MOVE:
                $nextNode = $this->current();
                break;
            case Direction::MOVE_UP:
                $nextNode = $this->getParent();
                break;
            case Direction::MOVE_DOWN:
                $nextNode = $this->getNextVisitableChild();
                /**
                 * we add a node to the node map every time we move down.  The type checker cannot see the
                 * logic in the subclass that guarantees $nextNode is not null if we are moving down
                 */
                assert(!is_null($nextNode));
                $this->nodeMap->setNode($nextNode, $this->current()?->getNodeId());
                break;
        }
        return $nextNode;
    }

    /**
     * getParent
     * @return NodeVisitableInterface|null
     */
    protected function getParent(): ?NodeVisitableInterface
    {
        if (is_null($nodeId = $this->current()?->getNodeId())) return null;
        return $this->nodeMap->getParent($nodeId);
    }

    private function endOfSearch(): bool
    {
        return is_null($this->current());
    }

    /**
     * allChildrenFullyVisited
     * @return bool
     * returns true if all children have been fully visited or if the node has no children
     */
    protected function allChildrenFullyVisited(): bool
    {
        return is_null($this->getNextVisitableChild());
    }

    /**
     * getNextVisitableChild
     * @return NodeVisitableInterface|null
     */
    protected function getNextVisitableChild(): ?NodeVisitableInterface
    {
        /** @var array<NodeVisitableInterface> $children */
        $children = $this->current()?->getChildrenArray() ?: [];

        $callback = function(NodeVisitableInterface $child) {
            return ($child->getVisitStatus() != VisitStatus::FULLY_VISITED);
        };
        return array_find($children, $callback);
    }
}
