<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use Exception;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\BadSearchLevelsException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeArrayException;
use pvc\struct\tree\err\InvalidNodeException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeHasInvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\RootCountForTreeException;
use pvc\struct\tree\err\SetNodesException;
use pvc\struct\tree\err\SetTreeIdException;

/**
 * Trait TreeAbstract
 * @template NodeType
 * @template NodeValueType
 * @implements TreeAbstractInterface<NodeType, NodeValueType>
 */
abstract class TreeAbstract implements TreeAbstractInterface
{
    /**
     * @var int
     */
    protected int $treeid;

    /**
     * @var NodeType|null
     */
    protected $root;

    /**
     * @var NodeType[]
     */
    protected array $nodes = [];

    public function __construct(int $treeid)
    {
        $this->setTreeId($treeid);
    }

    /**
     * all treeids are integers >= 0
     *
     * validateTreeId
     * @param int $nodeid
     * @return bool
     */
    private function validateTreeId(int $nodeid): bool
    {
        return 0 <= $nodeid;
    }

    /**
     * @function setTreeId
     * @param int $treeId
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function setTreeId(int $treeId): void
    {
        /**
         * treeid must pass validation
         */
        if (!$this->validateTreeId($treeId)) {
            throw new InvalidTreeidException($treeId);
        }

        /**
         * treeid can only be changed if the tree is empty
         */
        if (!$this->isEmpty()) {
            throw new SetTreeIdException();
        }
        $this->treeid = $treeId;
    }

    /**
     * @function getTreeId
     * @return int
     */
    public function getTreeId(): int
    {
        return $this->treeid;
    }

    /**
     * setRoot sets a reference to the root node of the tree
     *
     * @function setRoot
     * @param NodeType $node
     * @throws AlreadySetRootException
     */
    protected function setRoot($node): void
    {
        /**
         * if the root is already set, throw an exception
         */
        if (isset($this->root)) {
            throw new AlreadySetRootException();
        }

        $this->root = $node;
    }

    /**
     * @function getRoot
     * @return NodeType|null
     */
    public function getRoot()
    {
        return $this->root ?? null;
    }

    /**
     * addNodesToNodelistAndSetRoot
     * @param NodeType[] $nodeArray
     * @param string $classString
     * @throws AlreadySetRootException
     * @throws CircularGraphException
     * @throws InvalidNodeArrayException
     * @throws InvalidNodeException
     * @throws InvalidParentNodeException
     * @throws NodeHasInvalidTreeidException
     * @throws RootCountForTreeException
     * @throws SetNodesException
     */
    protected function addNodesToNodelistAndSetRoot(array $nodeArray, string $classString) : void
    {
        /**
         * tree must be empty before calling this method.
         */
        if (!$this->isEmpty()) {
            throw new SetNodesException();
        }

        /**
         * if the array is empty, just return because the rest of the method starts testing actual node data
         */
        if (empty($nodeArray)) {
            return;
        }

        /**
         * make sure each node in the array has TreenodeInterface, belongs in this tree, and has a key that matches
         * its nodeId
         */
        foreach ($nodeArray as $key => $node) {
            if (!$node instanceof $classString) {
                throw new InvalidNodeException();
            }
            if ($node->getTreeId() != $this->getTreeId()) {
                throw new NodeHasInvalidTreeidException($node->getNodeId(), $node->getTreeId(), $this->getTreeId());
            }
            if ($key != $node->getNodeId()) {
                throw new InvalidNodeArrayException($key, $node->getNodeId());
            }
        }

        /**
         * verify there is one root and that all non-null parent ids are in the array.
         */
        $rootCount = 0;
        foreach ($nodeArray as $node) {
            if ($node->isRoot()) {
                $root = $node;
                $rootCount++;
            } else {
                $parentId = $node->getParentId();
                if (!isset($nodeArray[$parentId])) {
                    throw new InvalidParentNodeException($parentId);
                }
            }
        }
        if ($rootCount != 1) {
            throw new RootCountForTreeException($rootCount);
        }

        /**
         * insure there are no circular references in the tree and ensure array is keyed properly
         */
        $keyedNodeArray = [];
        foreach ($nodeArray as $node) {
            $this->checkCircularity($node, $nodeArray);
            $keyedNodeArray[$node->getNodeId()] = $node;
        }

        /**
         * add nodes and set the root.
         * static analysis tools cannot see that $root must be set at this point.  The if statement is superfluous
         * but makes the static analysis warning go away
         */
        if (isset($root)) {
            $this->setRoot($root);
        }
        $this->nodes = $keyedNodeArray;
    }

    /**
     * addNodeToNodelistAndSetRoot
     * @param NodeType $node
     * @throws AlreadySetNodeidException
     * @throws AlreadySetRootException
     * @throws InvalidParentNodeException
     * @throws NodeHasInvalidTreeidException
     */
    protected function addNodeToNodelistAndSetRoot($node) : void
    {
        $nodeid = $node->getNodeId();

        /**
         * make sure nodeId does not already exist in the tree
         */
        if (!is_null($this->getNode($nodeid))) {
            throw new AlreadySetNodeidException($nodeid);
        }

        /**
         * make sure the treeid of the node matches the tree's treeId.  Use a strict comparison so that in the off
         * chance that the treeid is 0 and node's treeid is nul, we don't have a type casting problem.
         */
        if ($this->getTreeId() !== $node->getTreeId()) {
            throw new NodeHasInvalidTreeidException($node->getNodeId(), $node->getTreeId(), $this->getTreeId());
        }

        /**
         * if there's a parentid, make sure that exists in the tree as well
         */
        $parentid = $node->getParentId();
        if ((!is_null($parentid)) && (is_null($this->getNode($parentid)))) {
            throw new InvalidParentNodeException($parentid);
        }

        /**
         * set node as the root if it has root characteristics
         */
        if ($node->isRoot()) {
            $this->setRoot($node);
        }

        $this->nodes[$nodeid] = $node;
    }

    /**
     * @function getNode
     * @param int $nodeId
     * @return NodeType|null
     */
    public function getNode(int $nodeId)
    {
        return $this->nodes[$nodeId] ?? null;
    }

    /**
     * checkCircularity verifies there are no circularities in the tree structure.
     *
     * @function checkCircularity
     * @param NodeType $node
     * @param NodeType[] $nodeArray
     * @throws CircularGraphException
     */
    protected function checkCircularity($node, array $nodeArray): void
    {
        $nodeid = $node->getNodeId();
        $ancestorid = $node->getParentId();

        /**
         * if ancestor treeId is null then we have bubbled up to the root of the tree, so no circularities
         */
        while ($ancestorid !== null) {
            if ($nodeid == $ancestorid) {
                throw new CircularGraphException($nodeid);
            }
            /**
             * move ancestorid up the tree one more level and repeat
             */
            $ancestor = $nodeArray[$ancestorid];
            $ancestorid = $ancestor->getParentid();
        }
    }

    /**
     * @function getNodes
     * @return NodeType[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * hasNode does an object compare between its argument and each node in the tree, returning true
     * if it finds a match.  The $strict parameter controls whether the method uses "==" (all properties have the
     * same values) or "===" ($obj1 and $obj2 are the same instance).
     *
     * @function hasNode
     * @param NodeType|null $nodeToBeTested
     * @param bool $strict
     * @return bool
     */
    public function hasNode($nodeToBeTested = null, bool $strict = true): bool
    {
        foreach ($this->getNodes() as $node) {
            if ($node === $nodeToBeTested) {
                return true;
            }
        }
        return false;
    }

    /**
     * isEmpty tells you whether the tree has any nodes or not.
     *
     * @function isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return (0 == $this->nodeCount());
    }

    /**
     * @function nodeCount
     * @return int
     */
    public function nodeCount(): int
    {
        return count($this->nodes);
    }

    /**
     * deletes a node from the tree.
     *
     * If deleteBranchOK is true then node and all its descendants will be deleted as well.  If deleteBranchOK is false
     * and $nose is an interior node, then it throws an exception.
     *
     * @function deleteNode
     * @param NodeType $node
     * @param bool $deleteBranchOK
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode($node, bool $deleteBranchOK = false) : void
    {
        /**
         * make sure node is in the tree, and we are not trying to delete an interior node unless $deleteBranchOK is
         * true.
         */
        $this->verifyDeleteNodeInitialConditions($node, $deleteBranchOK);

        /**
         * if deleteBranchOK is true then recursively delete all descendants.  Finish by deleting the node.
         */
        $this->deleteNodeRecurse($node, $deleteBranchOK);

        /**
         * If this node happens to be the root of the tree, delete the root reference.
         */
        if ($node === $this->getRoot()) {
            $this->root = null;
        }
    }

    /**
     * @function verifyDeleteNodeInitialConditions
     * @param NodeType $node
     * @param bool $deleteBranch
     * @throws NodeNotInTreeException
     * @throws DeleteInteriorNodeException
     */
    protected function verifyDeleteNodeInitialConditions($node, bool $deleteBranch) : void
    {
        $nodeid = $node->getNodeId();

        /**
         * if the node is not in the tree, throw an exception
         */
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($this->getTreeId(), $nodeid);
        }

        /**
         * if this is an interior node and deleteBranchOK parameter is false, throw an exception
         */
        if (!$deleteBranch && $this->hasInteriorNodeWithId($nodeid)) {
            throw new DeleteInteriorNodeException($nodeid);
        }
    }


    /**
     * @function deleteNodeRecurse
     * @param NodeType $node
     * @param bool $deleteBranch
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    protected function deleteNodeRecurse($node, bool $deleteBranch): void
    {
        /**
         * if deleteBranchOK is true, delete all the children first.
         */
        if ($deleteBranch) {
            /**
             * unlike TreeOrder where the node can get its own children, in this implementation we have to go to the
             * tree and run through all the nodes to get the children.
             */
            $children = $this->getChildrenOf($node);
            foreach ($children as $child) {
                $this->deleteNodeRecurse($child, true);
            }
        }
        /**
         * remove the node from the nodelist
         */
        unset($this->nodes[$node->getNodeId()]);
    }

    /**
     * @function getTreeDepthFirst
     * @param NodeType|null $startNode     Defaults to the root node if not supplied
     * @param callable|null $callback               Defaults to true (return all nodes) if not supplied
     * @return NodeType[]
     * @throws NodeNotInTreeException
     */
    public function getTreeDepthFirst($startNode = null, callable $callback = null): array
    {
        $this->verifyTreeSearchInitialConditions($startNode, $callback);
        return $this->getTreeDepthFirstRecurse($startNode, $callback);
    }

    /**
     * getTreeDepthFirstRecurse does the actual work of traversing the tree.
     *
     * @function getTreeDepthFirstRecurse
     * @param NodeType $startNode
     * @param callable $callable
     * @return NodeType[]
     * @throws NodeNotInTreeException
     */
    protected function getTreeDepthFirstRecurse($startNode, callable $callable): array
    {
        $result = [];

        /**
         * if the filter callback returns true, add the node to the resultset.
         */
        if ($callable($startNode)) {
            $result[$startNode->getNodeId()] = $startNode;
        }

        /**
         * get the list of children and recurse on each child, merging the resultset arrays back together and
         * returning the merged resultset.
         */
        $children = $this->getChildrenOf($startNode);
        foreach ($children as $child) {
            $result = array_merge($result, $this->getTreeDepthFirstRecurse($child, $callable));
        }
        return $result;
    }

    /**
     * verifyTreeSearchInitialConditions is called by both getTreeDepthFirst and getTreeBreadthFirst
     *
     * If startnode is not supplied as a parameter, it supplies the root node of the tree as a default.  If
     * startnode is supplied, it verifies that startNode is in the tree.
     *
     * If the callback is not supplied, it provides one that returns true for all nodes.  It was tempting to try and
     * enforce the notion that the callback should return a boolean value, but after thinking about it, I think
     * that's too restrictive.  The callback is used as an argument to array_filter in the search and array_filter
     * only needs the callback to return a value that evaluates to true in order to include it in the resultset,
     * which is different from restricting it to returning a boolean value.
     *
     * verifyTreeSearchInitialConditions
     * @param NodeType|null $startNode
     * @param callable|null $callback
     * @throws Exception
     */
    protected function verifyTreeSearchInitialConditions(&$startNode = null, callable &$callback = null) : void
    {

        /**
         * start at the root of the tree unless a start node was supplied in the parameters
         */
        if (is_null($startNode)) {
            $startNode = $this->getRoot();
        }

        /**
         * If the startnode is not in the tree, throw an exception
         */
        if (!$this->hasNode($startNode)) {
            /** @var TreenodeAbstractInterface<NodeType, NodeValueType> $startNode */
            throw new NodeNotInTreeException($this->getTreeId(), $startNode->getNodeId());
        }

        /**
         * supply a default callback if not provided in the parameters
         */
        if (is_null($callback)) {
            $callback = function ($node): bool {
                return true;
            };
        }
    }

    /**
     * getTreeBreadthFirst allows you to search the tree from a given starting node using a breadth-first algorithm.
     *
     * The starting node would typically be the root, but it does not have to be.  Also, you can supply a callback
     * which returns a boolean indicating whether a node should be included in the resultset.  This allows you to
     * search the tree and filter the resultset according to a certain set of criteria.  You can also specify a
     * maximum number of maxLevels down the tree you want to go in the traversal.  If maxLevels is null, the search
     * goes to the bottom.  The returned resultset is an array of nodes where the key for each array element is its
     * nodeId.
     *
     * @function getTreeBreadthFirst
     * @param null $startNode
     * @param callable|null $callback
     * @param int|null $maxLevels
     * @return NodeType[]
     * @throws BadSearchLevelsException
     */
    public function getTreeBreadthFirst($startNode = null, callable $callback = null, int $maxLevels = null): array
    {

        $this->verifyTreeSearchInitialConditions($startNode, $callback);

        /**
         * throw an exception if $maxLevels is <= 0
         */
        if ((!is_null($maxLevels)) && ($maxLevels <= 0)) {
            throw new BadSearchLevelsException($maxLevels);
        }

        return $this->getTreeBreadthFirstRecurse([$startNode], $callback, $maxLevels);
    }

    /**
     * getTreeBreadthFirstRecurse does the actual work of traversing the tree.
     *
     * @function getTreeBreadthFirstRecurse
     * @param NodeType[] $result
     * @param callable $callback
     * @param int|null $maxLevels
     * @return NodeType[]
     */
    protected function getTreeBreadthFirstRecurse(array $result, callable $callback, int $maxLevels = null): array
    {
        /**
         * filter the existing results using the callback before recursing on the children.
         */
        $result = array_filter($result, $callback);

        /**
         * if we have gotten to max maxLevels of search (counting down) then return.  Very important to use strict
         * equals "===" because maxLevels can be null which is cast to 0 with "=="
         */
        if ($maxLevels === 0) {
            return $result;
        }

        /**
         * if maxLevels is being used, decrement it before recursing
         */
        if (!is_null($maxLevels)) {
            $maxLevels--;
        }

        /**
         * Get all the children of the current resultset.  This is clumsy but  phpstand does not recognize the array
         * [$this, 'getChildrenOf'] as callable
         *
         * @var callable $callable
         */
        $callable = [$this, 'getChildrenOf'];
        /** @var NodeType[] $allChildren */
        $allChildren = call_user_func_array('array_merge', array_map($callable, $result));

        /**
         * if there are children, recurse on them and merge into the current resultset.  If there are no remaining
         * children, just return the current resultset.
         */
        if (!empty($allChildren)) {
            return array_merge($result, $this->getTreeBreadthFirstRecurse($allChildren, $callback, $maxLevels));
        } else {
            return $result;
        }
    }

    /**
     * @function hasLeafWithId
     * @param ? int $nodeid
     * @return bool
     */
    public function hasLeafWithId(int $nodeid = null): bool
    {
        /**
         * if there is no such nodeId in the tree return false;
         */
        if (is_null($nodeid) || is_null($this->getNode($nodeid))) {
            return false;
        }

        /**
         * loop through all nodes.  If we find a node that has nodeId as its parentid then return false.
         */
        $result = true;
        foreach ($this->nodes as $possibleChild) {
            if ($possibleChild->getParentId() === $nodeid) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * @function hasInteriorNodeWithId
     * @param ? int $nodeid
     * @return bool
     */
    public function hasInteriorNodeWithId(int $nodeid = null): bool
    {
        /**
         * if there is no such nodeId in the tree return false;
         */
        if (is_null($nodeid) || is_null($this->getNode($nodeid))) {
            return false;
        }

        /**
         * loop through all nodes.  If we find a node whose parentid equals nodeId, return true;
         */
        $result = false;
        foreach ($this->nodes as $node) {
            if ($node->getParentId() === $nodeid) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * getLeaves returns an array of all nodes in the tree that do not have children
     *
     * @function getLeaves
     * @return NodeType[]
     */
    public function getLeaves(): array
    {
        $result = [];
        foreach ($this->nodes as $node) {
            if ($this->hasLeafWithId($node->getNodeid())) {
                $result[] = $node;
            }
        }
        return $result;
    }

    /**
     * getInteriorNodes returns an array of all nodes in the tree that have children
     *
     * @function getInteriorNodes
     * @return NodeType[]
     */
    public function getInteriorNodes(): array
    {
        $result = [];
        foreach ($this->nodes as $node) {
            if (!$this->hasLeafWithId($node->getNodeid())) {
                $result[] = $node;
            }
        }
        return $result;
    }
}
