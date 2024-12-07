<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\VisitStatus;

/**
 * Class SearchStrategyDepthFirstPostorder
 */
class SearchStrategyDepthFirstPostorder extends SearchStrategyDepthFirst
{
    public function rewind(): void
    {
        parent::rewind();
        /**
         * there's an initialization step of calling next().  This sets the current node properly
         * because the current node should be the start node only if we are preorder mode.  If we are post order
         * mode, we want to recurse to the bottom of the tree so that the first node returned is at the bottom of
         * the tree. Also, because current() is called right after rewind(), want to set the visit status of the
         * current node to fully visited
         */
        $this->next();
    }

    /**
     * getMovementDirection
     * @return Direction
     *
     * returns MOVE_DOWN if we should keep iterating by recursing down through child nodes,
     * returns STOP if we should stop iterating
     * returns MOVE_UP if we should continue iterating by returning up to the parent
     *
     * in post order mode, we go from never visited to partially visited and keep moving down.
     *
     * in post order mode we go from partially visited to fully visited if all the children have been visited
     * otherwise we move down.
     *
     * if a node is fully visited we always keep going up.
     *
     */
    protected function getMovementDirection(): Direction
    {
        return match ($this->currentNode->getVisitStatus()) {
            /**
             * keep going by recursing down through the children unless there are none or we hit max levels
             */
            VisitStatus::NEVER_VISITED => ($this->allChildrenFullyVisited() || $this->atMaxLevels()) ?
                Direction::DONT_MOVE :
                Direction::MOVE_DOWN,

            /**
             * if all the children have been visited we go to fully visited and stop, otherwise we move down.
             */
            VisitStatus::PARTIALLY_VISITED => ($this->allChildrenFullyVisited() || $this->atMaxLevels()) ?
                Direction::DONT_MOVE :
                Direction::MOVE_DOWN,

            /**
             * if the current node is fully visited we always move up
             */
            VisitStatus::FULLY_VISITED => Direction::MOVE_UP,
        };
    }
}
