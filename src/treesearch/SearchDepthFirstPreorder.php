<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeVisitableInterface;
use pvc\interfaces\struct\treesearch\VisitStatus;

/**
 * Class SearchStrategyDepthFirstPreorder
 * @template NodeType of NodeVisitableInterface
 *
 * @extends SearchDepthFirst<NodeType>
 */
class SearchDepthFirstPreorder extends SearchDepthFirst
{
    /**
     * getMovementDirection
     * @return Direction
     *
     * returns MOVE_DOWN if we should keep iterating by recursing down through child nodes,
     * returns STOP if we should stop moving through nodes
     * returns MOVE_UP if we should continue moving by returning up to the parent
     *
     * the goal is to stop every time we hit a node we have never visited.
     *
     * This method also changes the visit status of the current node.
     *
     * if node never visited, we go to partially visited and stop.
     *
     * if node partially visited and if all the children are fully visited, we go to fully visited and move up.
     * if a node is partially visited and not all the children are fully visited then we move down
     */
    protected function getMovementDirection(): Direction
    {
        assert(!is_null($this->current()));

        switch ($this->current()->getVisitStatus()) {

            /**
             * in preorder mode, stop when we first encounter a node.  There's an initialization condition to
             * account for:  rewind is called in the first iteration and next for all subsequent iterations.
             * Because current has been called before next the first time around, the start node has already been
             * returned, so we do not want to return it again.
             */
            case VisitStatus::NEVER_VISITED:
                $this->current()->setVisitStatus(VisitStatus::PARTIALLY_VISITED);
                $direction = ($this->current() == $this->getStartNode()) ? Direction::MOVE_DOWN : Direction::DONT_MOVE;
                break;

            /**
             * if all the children are fully visited, or we are at the max search level, then we move to full visited
             * otherwise we move down.  The default case should never be true, which is to say that we should never
             * be moving into a node that is fully visited.  The default case is only there to satisfy the type checker
             * that the $direction variable will have a value in all cases.
             */
            case VisitStatus::PARTIALLY_VISITED:
            default:
                if ($this->allChildrenFullyVisited() || $this->atMaxLevels()) {
                    $this->current()->setVisitStatus(VisitStatus::FULLY_VISITED);
                    $direction = Direction::MOVE_UP;
                } else {
                    $direction = Direction::MOVE_DOWN;
                }
                break;
        }
        return $direction;
    }
}
