<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeVisitableInterface;
use pvc\interfaces\struct\treesearch\VisitStatus;

/**
 * Class SearchStrategyDepthFirstPostorder
 * @template NodeType of NodeVisitableInterface
 *
 * @extends SearchDepthFirst<NodeType>
 */
class SearchDepthFirstPostorder extends SearchDepthFirst
{
    public function rewind(): void
    {
        parent::rewind();
        /**
         * The rewind method of the parent sets the current node to be the start node. This is correct for
         * breadth first and depth first preorder searches.  But for depth-first-postorder searches, we want to recurse
         * to the bottom of the tree so that the first node returned is at the bottom of the tree.
         */
        $this->next();
    }

    /**
     * getMovementDirection
     *
     * @return Direction
     *
     * returns MOVE_DOWN if we should keep iterating by recursing down through child nodes,
     * returns STOP if we should stop iterating
     * returns MOVE_UP if we should continue iterating by returning up to the parent
     *
     * in post order mode, we go from never visited to partially visited and keep moving down if we can move down.
     * if we cannot, we go to fully visited and stop
     *
     * in post order mode we go from partially visited to fully visited if all the children have been visited
     * otherwise we move down.
     *
     * if a node is fully visited we always keep going up.
     *
     */
    protected function getMovementDirection(): Direction
    {
        assert(!is_null($this->current()));

        switch ($this->current()->getVisitStatus()) {
            case VisitStatus::NEVER_VISITED:
                if ($this->allChildrenFullyVisited() || $this->atMaxLevels()) {
                    $this->current()->setVisitStatus(
                        VisitStatus::FULLY_VISITED
                    );
                    $direction = Direction::DONT_MOVE;
                } else {
                    $this->current()->setVisitStatus(
                        VisitStatus::PARTIALLY_VISITED
                    );
                    $direction = Direction::MOVE_DOWN;
                }
                break;

            case VisitStatus::PARTIALLY_VISITED:
                if ($this->allChildrenFullyVisited() || $this->atMaxLevels()) {
                    $this->current()->setVisitStatus(
                        VisitStatus::FULLY_VISITED
                    );
                    $direction = Direction::DONT_MOVE;
                } else {
                    $direction = Direction::MOVE_DOWN;
                }
                break;

            case VisitStatus::FULLY_VISITED:
                $direction = Direction::MOVE_UP;
                break;
        }

        return $direction;
    }
}
