<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;

/**
 * Class SearchStrategyDepthFirst
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @extends SearchStrategyAbstract<PayloadType, NodeType, TreeType, CollectionType>
 * @implements SearchStrategyInterface<PayloadType, NodeType, TreeType, CollectionType>
 */
class SearchStrategyDepthFirst extends SearchStrategyAbstract implements SearchStrategyInterface
{

    /**
     * preorder means that a node is added to the result set upon its initial visitation, whereas post order is when
     * the node is added to the resultset upon its second (last) visitation.
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
     * clearVisitCounts
     */
    protected function clearVisitCounts(): void
    {
        if ($this->getStartNode()) {
            $this->clearVisitCountsRecurse($this->startNode);
        }
    }

    /**
     * clearVisitCountsRecurse
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $node
     */
    protected function clearVisitCountsRecurse(TreenodeAbstractInterface $node): void
    {
        $node->clearVisitCount();
        /** @var NodeType $child */
        foreach ($node->getChildren() as $child) {
            $this->clearVisitCountsRecurse($child);
        }
    }

    /**
     * rewind
     */
    public function rewind(): void
    {
        if ($this->currentNode = $this->getStartNode()) {
            $this->clearVisitCounts();
            /**
             * there's an initialization step of calling getNextNodeProtected.  This sets the current node properly
             * because the current node should be the start node if we are preorder mode.  If we are post order mode,
             * we want to recurse to the bottom of the tree so that the first node returned is at the bottom of the tree.
             */
            $this->currentNode = $this->getNextNodeProtected();
        }
    }

    /**
     * getNextNodeProtected
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null
     *
     * preorder mode means that you return the node the first time you visit it.
     * postorder mode means you return the node the last time you visit it.
     *
     * so the algorithm is that you add 1 to the visit count the first time you visit the node.  You add one more to
     * the visit count when all the children have been visited twice.
     */
    protected function getNextNodeProtected(): TreenodeAbstractInterface|null
    {
        if (!$this->valid()) {
            return null;
        }
        /**
         * assert helps the static analysis type checker
         */
        assert($this->currentNode instanceof TreenodeAbstractInterface);

        /**
         * visit count == 0 means this is the first time we have visited this node.
         */
        if ($this->currentNode->getVisitCount() == 0) {
            $this->currentNode->addVisit();
            /**
             * if we are in preorder mode, then return this node
             */
            if ($this->getOrdering() == self::PREORDER) {
                return $this->currentNode;
            } /**
             * otherwise we are in post order mode.  Recursively visit all the children of this node, getting us
             * to the bottom of the tree in this branch before returning any nodes.
             */
            else {
                /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $child */
                foreach ($this->currentNode->getChildren() as $child) {
                    $this->currentNode = $child;
                    return $this->getNextNodeProtected();
                }
            }
        }

        if ($this->currentNode->getVisitCount() == 1) {
            /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $child */
            foreach ($this->currentNode->getChildren() as $child) {
                if ($child->getVisitCount() < 2) {
                    $this->currentNode = $child;
                    return $this->getNextNodeProtected();
                }
            }
            /**
             * if we got here then either the current node has no children or they all have been visited twice and
             * returned.  Bump the visit count on this node.  If we are in post order mode, return this node.
             * Otherwise, set the node to be the parent if possible and recurse
             */
            $this->currentNode->addVisit();
            if ($this->getOrdering() == self::POSTORDER) {
                return $this->currentNode;
            } else {
                $this->currentNode = $this->currentNode->getParent();
                return $this->getNextNodeProtected();
            }
        }
        /**
         * visit count == 2
         */
        $this->currentNode = $this->currentNode->getParent();
        return $this->getNextNodeProtected();
    }

    /**
     * @function getNodesProtected
     *
     * getNodesDepthFirst recursively returns an array of nodes via a depth first search starting at $node.
     *
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    protected function getNodesProtected(): array
    {
        $startNode = $this->getStartNode();
        $result = $this->getNodesRecurse($startNode);
        return $result;
    }

    /**
     * getNodesRecurse
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>|null $node
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>>
     */
    protected function getNodesRecurse(TreenodeAbstractInterface|null $node): array
    {
        $result = [];

        if (!$node) {
            return $result;
        }

        if ($this->ordering == self::PREORDER) {
            $result[] = $node;
        }

        /**
         * get the list of children and recurse on each child, merging the result set arrays back together and
         * returning the merged result set.
         * @var NodeType $child
         */
        foreach ($node->getChildren()->getElements() as $child) {
            $result = array_merge($result, $this->getNodesRecurse($child));
        }

        if ($this->ordering == self::POSTORDER) {
            $result[] = $node;
        }

        return $result;
    }
}
