<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\search;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\struct\tree\err\InvalidDepthFirstSearchOrderingException;

/**
 * Class SearchStrategyDepthFirst
 * @template NodeType of TreenodeAbstractInterface
 * @extends SearchStrategyAbstract<NodeType>
 * @implements SearchStrategyInterface<NodeType>
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

    /**
     * @var NodeType|null
     */
    private ?TreenodeAbstractInterface $currentNode;


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
     * resetSearch
     */
    public function resetSearch(): void
    {
        $this->clearVisitCounts();
        $this->currentNode = $this->getStartNode();
    }

    /**
     * getNextNodeProtected
     * @return NodeType|null
     */
    protected function getNextNodeProtected(): TreenodeAbstractInterface|null
    {
        /**
         * if current node is null then all nodes below have been visited twice - we are all done
         */
        if (is_null($this->currentNode)) {
            return null;
        }

        /**
         * visit count == 0 means we have never visited this node.  Bump the visit count and return the node if we
         * are in preorder.
         */
        if ($this->currentNode->getVisitCount() == 0) {
            $this->currentNode->addVisit();
            if ($this->ordering == self::PREORDER) {
                return $this->currentNode;
            }
        }


        /**
         * visit count == 1 means we have been here once before.  Check and see if all the children have been fully
         * visited. If not, find the first one that has not been fully visited, set that to be the current node and
         * recurse down the tree.  If all the children have been fully visited, then bump the visit count on the
         * current node.
         */
        if (($this->currentNode->getVisitCount() == 1)) {
            $children = $this->currentNode->getChildren();
            /** @var NodeType $child */
            foreach ($children as $child) {
                if ($child->getVisitCount() < 2) {
                    $this->currentNode = $child;
                    return $this->getNextNode();
                }
            }
            /**
             * no child nodes left to visit
             */
            $this->currentNode->addVisit();
        }

        /**
         * visit count == 2 means all the children of current node have been visited twice and so has the current
         * node.
         */
        if ($this->currentNode->getVisitCount() == 2) {
            /** @var NodeType $parent */
            $parent = $this->currentNode->getParent();
            if ($this->ordering == self::POSTORDER) {
                /**
                 * we want to return this node as the result, but we need to set the current node to be the parent
                 * first.  So we have to save the current node in the $resuit variable first.
                 */
                $result = $this->currentNode;
                $this->currentNode = $parent;
                return $result;
            } else {
                $this->currentNode = $parent;
            }
        }

        return $this->getNextNode();
    }

    /**
     * @function getNodesProtected
     *
     * getNodesDepthFirst recursively returns an array of nodes via a depth first search starting at $node.
     *
     * @return array<NodeType>
     */
    protected function getNodesProtected(): array
    {
        /** @var NodeType $startNode */
        $startNode = $this->getStartNode();
        /** @var array<NodeType> $result */
        $result = $this->getNodesRecurse($startNode);
        return $result;
    }

    /**
     * getNodesRecurse
     * @param NodeType $node
     * @return array<NodeType>
     */
    protected function getNodesRecurse(TreenodeAbstractInterface $node): array
    {
        $result = [];

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
