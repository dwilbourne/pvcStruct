<?php declare(strict_types = 1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\_TreeXData;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;

/**
 * Class TreeOrdered
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 * @template NodeValueType
 * @extends TreeAbstract<TreenodeOrderedInterface, NodeValueType>
 * @implements TreeOrderedInterface<NodeValueType>
 */
class TreeOrdered extends TreeAbstract implements TreeOrderedInterface
{
    /**
     * @function addNode
     * @param TreenodeOrderedInterface<NodeValueType> $node
     * @throws AlreadySetNodeidException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    public function addNode($node): void
    {
        /**
         * node is added to the nodes property of the tree and if node is the root, the root gets set as well.
         */
        $this->addNodeToNodelistAndSetRoot($node);
        /**
         * node sets the references to parent and tree objects
         */
        $node->setReferences($this);
        /**
         * add node to the child list of the parent.  Node has an index property which is used to position the node
         * correctly in the list.  Need to coalesce getIndex to 0 because the static analyzers see that it can return
         * null (but which cannot be true at this point in the code).
         */
        if ($parent = $node->getParent()) {
            $parent->getChildren()->add($node->getIndex() ?? 0, $node);
        }
    }

    /**
     * deletes a node from the tree.
     *
     * If deleteBranchOK is true then node and all its descendants will be deleted as well.  If deleteBranchOK is false
     * and $nose is an interior node, then it throws an exception.
     *
     * @function deleteNode
     * @param TreenodeOrderedInterface<NodeValueType> $node
     * @param bool $deleteBranchOK
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode($node, bool $deleteBranchOK = false) : void
    {
        parent::deleteNode($node, $deleteBranchOK);
        /**
         * delete reference to node in the parent's child list.  If this node happens to be the root, getParent
         * returns null so there are no children to delete.  Also, throwing in a test that getIndex returns an int
         * and not null because otherwise the static analyzers complain....
         */
        if (($parent = $node->getParent()) && (!is_null($node->getIndex()))) {
            $parent->getChildren()->delete($node->getIndex());
        }
    }

    /**
     * @function setNodes
     * @param TreenodeOrderedInterface<NodeValueType>[] $nodeArray
     * @throws AlreadySetNodeidException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    public function setNodes(array $nodeArray): void
    {
        /**
         * add all the nodes to the array and set the root
         */
        $this->addNodesToNodelistAndSetRoot($nodeArray, TreenodeOrderedInterface::class);

        /**
         * set up all the references to actual objects in each node
         */
        foreach ($this->nodes as $node) {
            $node->setReferences($this);
        }

        /**
         * Get all the children of each node and populate the child list in the correct order.  This is a pretty ugly
         * N-squared algorithm.  It could be improved by storing all the child nodeids in the same array as the ids
         * for the node itself.  For example, if the root node had 3 children with ids 2, 3, and 7, the array storing
         * the root's information could look like (0, null, 1, 2, 3, 7).  In other words, the first 3 elements are
         * nodeid, parentid, and treeid.  All remaining elements would be children and would be in the order in which
         * they are to be inserted into the child list.  That would make the data store logic more complicated
         * because the arrays for the nodes would have different lengths but this logic to insert children gets easier
         * and much faster.
         */
        $callback = function (TreenodeOrderedInterface $node1, TreenodeOrderedInterface $node2) {
            /**
             * spaceship operator returns -1 if node1 < node2, 0 if they are equal, +1 if Node1 > node2
             */
                return ($node1->getIndex() <=> $node2->getIndex());
        };

        $i = 0;
        foreach ($this->nodes as $parent) {
            $children = [];
            foreach ($this->nodes as $child) {
                /**
                 * once again, use a strict equals to distinguish between 0 and null
                 */
                if ($child->getParentId() === $parent->getNodeId()) {
                    $children[] = $child;
                }
            }
            /**
             * usort is an "in place" sort, e.g. sorts the argument and does not return any value.  It destroys any
             * original keys and creates new keys starting at 0.  This is perfect for inserting into an ordered list.
             */
            usort($children, $callback);
            foreach ($children as $index => $listElement) {
                $parent->getChildren()->add($index, $listElement);
                $i++;
            }
        }
    }

    /**
     * @function getChildrenOf
     * @param TreenodeOrderedInterface<NodeValueType> $parent
     * @return TreenodeOrderedInterface<NodeValueType>[]
     * @throws NodeNotInTreeException
     */
    public function getChildrenOf($parent): array
    {
        if (!$this->hasNode($parent)) {
            throw new NodeNotInTreeException($this->getTreeId(), $parent->getNodeId());
        }
        return $parent->getChildren()->getElements();
    }

    /**
     * @function getParentOf
     * @param TreenodeOrderedInterface<NodeValueType> $node
     * @return TreenodeOrderedInterface<NodeValueType>|null
     * @throws NodeNotInTreeException
     */
    public function getParentOf($node): ?TreenodeOrderedInterface
    {
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($this->getTreeId(), $node->getNodeId());
        }
        return $node->getParent();
    }
}
