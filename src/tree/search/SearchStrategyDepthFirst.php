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
     * the node is added to the result set upon its second (last) visitation.
     */
    public const PREORDER = 0;

    public const POSTORDER = 1;

    /**
     * @var int
     */
    protected int $ordering = self::PREORDER;

    /**
     * @var array|int[]
     */
    private array $validOrders = [self::PREORDER, self::POSTORDER];

    public function getOrdering(): int
    {
        return $this->ordering;
    }

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
     * orderingIsValid
     * @param $ordering
     * @return bool
     */
    private function orderingIsValid(int $ordering): bool
    {
        return in_array($ordering, $this->validOrders);
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
     * next
     */
    public function next(): void
    {
        if ($this->getOrdering() == self::PREORDER) {
            $this->nextPreorder();
        } else {
            $this->nextPostorder();
        }
    }

    /**
     * addChildOfCurrentToDepthNodeMap
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $child
     */
    protected function addChildOfCurrentToDepthNodeMap(TreenodeAbstractInterface $child): void
    {
        $parentDepth = $this->getNodeDepthMap()->getNodeDepth($this->getCurrentNode()->getNodeId());
        $this->getNodeDepthMap()->setNodeDepth($child->getNodeId(), $parentDepth + 1);
    }

    /**
     * nextPostorder
     *
     * postorder mode means you stop (return) just after the node is fully visited.
     */
    protected function nextPostorder(): void
    {
        if ($this->currentNode->getVisitStatus() == TreenodeAbstract::NEVER_VISITED) {
            /**
             * set the status to partially visited and drop through to the next logic block which deals with
             * partially visited nodes.
             */
            $this->currentNode->setVisitStatus(TreenodeAbstract::PARTIALLY_VISITED);
        }

        if ($this->currentNode->getVisitStatus() == TreenodeAbstract::PARTIALLY_VISITED) {
            /**
             * since we are in postorder mode, we have not yet found the "next" node.  If this node has any children
             * which are not yet fully visited, then the next node is one of this node's descendants, provided that
             * we are not at the max level permitted.
             */
            if (!$this->atMaxLevel()) {
                /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $child */
                foreach ($this->currentNode->getChildren() as $child) {
                    /**
                     * add the child to the nodeDepthMap
                     */
                    $this->addChildOfCurrentToDepthNodeMap($child);

                    if ($child->getVisitStatus() < TreenodeAbstract::FULLY_VISITED) {
                        $this->setCurrentNode($child);
                        $this->incrementCurrentLevel();
                        $this->next();
                        /**
                         * make sure we do not loop through the rest of the children
                         */
                        return;
                    }
                }
            }
            /**
             * if we got here, then either this node has no children, all of its children have been fully visited,
             * or we are at the maximum permitted level in the tree.
             * This is the node we want to keep as the current node.  Set the status and return.
             */
            $this->getCurrentNode()->setVisitStatus(TreenodeAbstract::FULLY_VISITED);
            return;
        }

        /**
         * If this node has been fully visited then so have all its children.  Go up one level in the tree if possible
         * and recurse.
         */
        $parent = $this->getCurrentNode()->getParent();
        $this->decrementCurrentLevel();

        if ($parent) {
            $this->setCurrentNode($parent);
            $this->next();
        } else {
            $this->valid = false;
        }
    }

    /**
     * nextPreorder
     * preorder mode means that you return the node the first time you visit it.
     */
    protected function nextPreorder(): void
    {
        if ($this->getCurrentNode()->getVisitStatus() == TreenodeAbstract::NEVER_VISITED) {
            /**
             * set the status to partially visited and stop (e.g. return)
             */
            $this->currentNode->setVisitStatus(TreenodeAbstract::PARTIALLY_VISITED);
            return;
        }

        if ($this->getCurrentNode()->getVisitStatus() == TreenodeAbstract::PARTIALLY_VISITED) {
            if (!$this->atMaxLevel()) {
                /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $child */
                foreach ($this->getCurrentNode()->getChildren() as $child) {
                    /**
                     * add child to depth node map
                     */
                    $this->addChildOfCurrentToDepthNodeMap($child);

                    if ($child->getVisitStatus() < TreenodeAbstract::FULLY_VISITED) {
                        $this->setCurrentNode($child);
                        $this->incrementCurrentLevel();
                        $this->next();
                        return;
                    }
                }
            }
        }

        /**
         * if we got here then either the current node has no children, or they all have been fully visited,
         * or we are the maximum permitted level in the tree.
         * Set the status on this node to fully visited, set the current node to be the parent (if
         * possible) and recurse on the parent
         */
        $this->getCurrentNode()->setVisitStatus(TreenodeAbstract::FULLY_VISITED);
        $parent = $this->getCurrentNode()->getParent();
        $this->decrementCurrentLevel();

        if ($parent) {
            $this->setCurrentNode($parent);
            $this->next();
        } else {
            $this->valid = false;
        }
    }
}
