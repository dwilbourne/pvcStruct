<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\search\VisitStatus;

/**
 * Class SearchStrategyDepthFirstPreorder
 */
class SearchDepthFirstPreorder extends SearchDepthFirst
{
    /**
     * getMovementDirection
     * @return Direction
     *
     * returns MOVE_DOWN if we should keep iterating by recursing down through child nodes,
     * returns STOP if we should stop iterating
     * returns MOVE_UP if we should continue iterating by returning up to the parent
     *
     * if node never visited, we go to partially visited and stop.
     *
     * if node partially visited and if all the children are fully visited we move up, otherwise we move down
     *
     * if a node is fully visited we always keep going up.
     */
    protected function getMovementDirection(): Direction
    {
        return match ($this->currentNode->getVisitStatus()) {
            /**
             * in preorder mode, stop when we first encounter a node
             */
            VisitStatus::NEVER_VISITED => Direction::DONT_MOVE,

            /**
             * if mode partially visited and if all the children are fully visited, then we go to fully visited and
             * move up, otherwise we move down (if possible)
             */
            VisitStatus::PARTIALLY_VISITED =>
            ($this->allChildrenFullyVisited() || $this->atMaxLevels()) ?
                Direction::MOVE_UP :
                Direction::MOVE_DOWN,

            /**
             * if the current node is fully visited we always move up
             */
            VisitStatus::FULLY_VISITED => Direction::MOVE_UP,
        };
    }
}
