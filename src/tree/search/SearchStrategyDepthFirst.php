<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\search\NodeSearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;
use pvc\struct\tree\node\TreenodeAbstract;

/**
 * Class SearchStrategyDepthFirst
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @extends SearchStrategyAbstract<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 * @implements NodeSearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class SearchStrategyDepthFirst extends SearchStrategyAbstract implements NodeSearchStrategyInterface
{
    /**
     * preorder means that a node is added to the result set upon its initial visitation, whereas post order is when
     * the node is added to the result set upon its last visitation.
     */
    public const PREORDER = 0;

    public const POSTORDER = 1;

    private int $ordering = self::PREORDER;
    /**
     * @var array|int[]
     */
    private array $validOrders = [self::PREORDER, self::POSTORDER];


    /**
     * setOrdering
     * @param int $ordering
     */
    public function setOrdering(int $ordering): void
    {
        if (!$this->orderingIsValid($ordering)) {
            throw new InvalidDepthFirstSearchOrderingException();
        }
        $this->ordering = $ordering;
    }

    /**
     * getOrdering
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * orderingIsValid
     * @param $ordering
     * @return bool
     */
    private function orderingIsValid(int $ordering): bool
    {
        return in_array($ordering, $this->validOrders);
    }

    private function preorder(): bool
    {
        return ($this->ordering == 0);
    }

    private function postorder(): bool
    {
        return ($this->ordering == 1);
    }

    /**
     * clearVisitStatusRecurse
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    protected function clearVisitStatusRecurse(TreenodeAbstractInterface $node): void
    {
        $node->setVisitStatus(TreenodeAbstract::NEVER_VISITED);
        /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $child */
        foreach ($node->getChildren() as $child) {
            $this->clearVisitStatusRecurse($child);
        }
    }

    /**
     * rewind
     */
    public function rewind(): void
    {
        parent::rewind();
        $this->clearVisitStatusRecurse($this->getStartNode());
        /**
         * there's an initialization step of calling next().  This sets the current node properly
         * because the current node should be the start node only if we are preorder mode.  If we are post order
         * mode, we want to recurse to the bottom of the tree so that the first node returned is at the bottom of
         * the tree.
         */
        $this->next();
    }

    /**
     * addNodeToDepthMap
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     */
    protected function addNodeToDepthMap(TreenodeAbstractInterface $node): void
    {
        $parentDepth = $node->getParentId() ? $this->getNodeDepthMap()->getNodeDepth($node->getParentId()) : -1;
        $this->getNodeDepthMap()->setNodeDepth($node->getNodeId(), $parentDepth + 1);
    }

    /**
     * allChildrenFullyVisited
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @return bool
     */
    protected function allChildrenFullyVisited(TreenodeAbstractInterface $node): bool
    {
        /**
         * @var callable(bool, PayloadType):bool
         */
        $callback = function (bool $carry, TreenodeAbstractInterface $node) {
            return $carry && $node->fullyVisited();
        };
        $childrenArray = $node->getChildren()->getElements();
        return array_reduce($childrenArray, $callback, true);
    }

    /**
     * setNodeVisitStatus
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @return bool
     */
    protected function setNodeVisitStatus(TreenodeAbstractInterface $node): bool
    {
        /**
         * if we have exceeded the max permissible search depth, set the visit status of the node to fully visited
         * and return false so that we keep searching for the next node
         */
        if ($this->exceededMaxLevels()) {
            $node->setVisitStatus(TreenodeAbstract::FULLY_VISITED);
            return false;
        }

        /**
         * if this is the first time we have ever visited the node, set the status to partially visited
         */
        if ($node->neverVisited()) {
            $node->setVisitStatus(TreenodeAbstract::PARTIALLY_VISITED);
            /**
             * stop (return true) if we are in preorder mode otherwise keep searching
             */
            return $this->preorder();
        }

        /**
         * if a) all the children have been fully visited or b) node has no children, or c) we are at the maximum
         * permissible depth of the search, then set the status of this node to fully visited.  We only need to examine
         * partially visited nodes: if the node is fully visited then we already know that all its children have been
         * fully visited
         */
        if ($node->partiallyVisited() && $this->allChildrenFullyVisited($node)) {
            $node->setVisitStatus(TreenodeAbstract::FULLY_VISITED);
            /**
             * stop (return true) if we are in postorder mode otherwise keep searching
             */
            return $this->postorder();
        }

        /**
         * the node is partially visited but not all the children are fully visited. Keep searching for the next node
         */
        return false;
    }

    /**
     * next
     */
    public function next(): void
    {
        /**
         * check the current node, stop if we are supposed to or keep going in the recursive search.  We stop if one of
         * two things are true: 1) we are in preorder mode and this is the first time we have visited the node or
         * 2) we are in postorder mode and this is the last time that we will visit the node.  In the former case,
         * the visit status of the current node will be partially visited, in the latter case the visit status will be
         * fully visited.
         */
        if ($this->setNodeVisitStatus($this->getCurrentNode())) {
            return;
        }

        /**
         * in other words, you cannot get to this point with the current node having a status of never visited.  Also,
         * we know we are supposed to keep searching, which means recursing on the children if we can.
         */
        /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $child */
        foreach ($this->currentNode->getChildren() as $child) {
            /**
             * only process the child if it has not been fully visited
             */
            if (!$child->fullyVisited()) {
                $this->setCurrentNode($child);
                $this->incrementCurrentLevel();
                $this->addNodeToDepthMap($child);
                /**
                 * either we stop or we recurse on the child
                 */
                if (!$this->setNodeVisitStatus($child)) {
                    $this->next();
                }
                return;
            }
        }

        /**
         * It's not particularly obvious, but if we got this far in the method, then we know that the current node
         * has a visit status of fully visited and so do all of its children.
         *
         * Go up one level in the tree if possible and recurse.
         */
        if ($parent = $this->getCurrentNode()->getParent()) {
            $this->setCurrentNode($parent);
            $this->decrementCurrentLevel();
            $this->next();
        } else {
            $this->valid = false;
        }
    }
}
