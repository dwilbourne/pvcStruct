<?php declare(strict_types = 1);

namespace pvc\struct\tree\tree;

use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeDataException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\iface\tree\TreeOrderedInterface;
use pvc\struct\tree\iface\node\TreenodeOrderedInterface;

/**
 * Class Tree
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 */
class TreeOrdered implements TreeOrderedInterface
{

    /**
     * @var int
     */
    protected int $treeid;

    /**
     * @var TreenodeOrderedInterface|null
     */
    protected ?TreenodeOrderedInterface $root;

    /**
     * @var TreenodeOrderedInterface[]
     */
    protected array $nodes = [];

    /**
     * @function getTreeId
     * @return int
     */
    public function getTreeId() : int
    {
        return $this->treeid;
    }

    /**
     * @function setTreeId
     * @param int $id
     */
    public function setTreeId(int $id): void
    {
        $this->treeid = $id;
    }

    /**
     * @function getRoot
     * @return TreenodeOrderedInterface|null
     */
    public function getRoot(): ?TreenodeOrderedInterface
    {
        return $this->root;
    }

    /**
     * @function setRoot
     * @param TreenodeOrderedInterface $node
     * @throws AlreadySetRootException
     */
    protected function setRoot(TreenodeOrderedInterface $node) : void
    {
        if (isset($this->root)) {
            throw new AlreadySetRootException();
        }
        $this->root = $node;
    }

    /**
     * @function hasNode
     * @param TreenodeOrderedInterface $node
     * @return bool
     */
    public function hasNode(TreenodeOrderedInterface $node): bool
    {
        if (!isset($this->nodes[$node->getNodeId()])) {
            return false;
        }
        $ref = $this->nodes[$node->getNodeId()];
        return $node === $ref;
    }

    /**
     * @function getNode
     * @param int $nodeid
     * @return TreenodeOrderedInterface|null
     */
    public function getNode(int $nodeid): ?TreenodeOrderedInterface
    {
        if (!isset($this->nodes[$nodeid])) {
            return null;
        }
        return $this->nodes[$nodeid];
    }

    /**
     * @function getNodes
     * @return TreenodeOrderedInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @function isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !isset($this->root);
    }

    /**
     * @function hydrateNodes
     * @param array $nodeCollection
     * @throws AlreadySetNodeidException
     * @throws InvalidNodeDataException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    public function hydrateNodes(array $nodeCollection): void
    {
        // insure that $nodeCollection is properly keyed
        $nodeArray = [];
        foreach ($nodeCollection as $node) {
            if (!$node instanceof TreenodeOrderedInterface) {
                throw new InvalidNodeDataException();
            }
            $nodeArray[$node->getNodeId()] = $node;
        }
        foreach ($nodeArray as $node) {
            $this->addNodeHelper($node, $nodeArray);
        }

        $comparator = function (TreenodeOrderedInterface $nodeA, TreenodeOrderedInterface $nodeB): int {
            return $nodeA->getHydrationIndex() <=> $nodeB->getHydrationIndex();
        };
        uasort($this->nodes, $comparator);

        foreach ($this->nodes as $node) {
            $node->setReferences($this);
        }
    }

    /**
     * @function dehydrateNodes
     * @return array
     */
    public function dehydrateNodes(): array
    {
        $nodeData = [];
        foreach ($this->nodes as $node) {
            $nodeData[$node->getNodeId()] = $node->dehydrate();
        }
        return $nodeData;
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
     * @function getChildrenOf
     * @param TreenodeOrderedInterface $parent
     * @return array[TreenodeOrderedInterface]
     * @throws NodeNotInTreeException
     */
    public function getChildrenOf(TreenodeOrderedInterface $parent): array
    {
        if (!$this->hasNode($parent)) {
            throw new NodeNotInTreeException($parent->getParentId());
        }
        return $parent->getChildren()->getElements();
    }

    /**
     * @function getParentOf
     * @param TreenodeOrderedInterface $node
     * @return TreenodeOrderedInterface|null
     * @throws NodeNotInTreeException
     */
    public function getParentOf(TreenodeOrderedInterface $node): ?TreenodeOrderedInterface
    {
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($node->getNodeId());
        }
        if ($node === $this->getRoot()) {
            return null;
        }
        return $node->getParent();
    }

    /**
     * @function hasLeafWithId
     * @param int $nodeid
     * @return bool
     */
    public function hasLeafWithId(int $nodeid): bool
    {
        if (is_null($node = $this->getNode($nodeid))) {
            return false;
        }
        /** @phpstan-ignore-next-line */
        return $node->isLeaf();
    }

    /**
     * @function hasInteriorNodeWithId
     * @param int $nodeid
     * @return bool
     */
    public function hasInteriorNodeWithId(int $nodeid): bool
    {
        if (is_null($node = $this->getNode($nodeid))) {
            return false;
        }
        /** @phpstan-ignore-next-line */
        return $node->isInteriorNode();
    }

    /**
     * @function addNode
     * @param TreenodeOrderedInterface $node
     * @throws AlreadySetNodeidException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    public function addNode(TreenodeOrderedInterface $node): void
    {
        $this->addNodeHelper($node, $this->nodes);
        $node->setReferences($this);
        if (!is_null($node->getHydrationIndex())) {
            $node->setIndex($node->getHydrationIndex());
        }
    }

    /**
     * this routine is not only used by addNode but also by hydrate.  The difference is that when being
     * called by addNode, the nodeArray argument is $this->nodes, whereas when being called by hydrate,
     * $nodeArray is the array of nodes being imported.  This allows us to hydrate the tree having the nodes
     * in any arbitrary order instead of forcing that we add the root first and then add everything in a
     * breadth-first fashion to insure that all parent references are valid.
     *
     * @function addNodeHelper
     * @param TreenodeOrderedInterface $node
     * @param array $nodeArray
     * @throws AlreadySetNodeidException
     * @throws AlreadySetRootException
     * @throws CircularGraphException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    protected function addNodeHelper(TreenodeOrderedInterface $node, array $nodeArray) : void
    {
        $nodeid = $node->getNodeId();
        if ($this->hasNode($node)) {
            throw new AlreadySetNodeidException($node->getNodeId());
        }
        if ($this->getTreeId() != $node->getTreeId()) {
            throw new InvalidTreeidException($node->getNodeId(), $node->getTreeId(), $this->getTreeId());
        }
        $this->nodes[$nodeid] = $node;

        $parentid = $node->getParentId();
        if (is_null($parentid)) {
            $this->setRoot($node);
        }
        if (!is_null($parentid) && !isset($nodeArray[$parentid])) {
            throw new InvalidParentNodeException($parentid);
        }
        $this->checkCircularity($node, $nodeArray);
    }

    /**
     * verify there are no circularities in the tree structure.
     * @function checkCircularity
     * @param TreenodeOrderedInterface $node
     * @param array $nodeArray
     * @throws CircularGraphException
     */
    protected function checkCircularity(TreenodeOrderedInterface $node, array $nodeArray) : void
    {
        $nodeid = $node->getNodeId();
        $ancestorid = $node->getParentId();
        while ($ancestorid !== null) {
            if ($nodeid == $ancestorid) {
                throw new CircularGraphException($nodeid);
            }
            $ancestor = $nodeArray[$ancestorid];
            $ancestorid = $ancestor->getParentid();
        }
    }

    /**
     * @function deleteNode
     * @param TreenodeOrderedInterface $node
     * @param bool $deleteBranchOK
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode(TreenodeOrderedInterface $node, bool $deleteBranchOK = false): void
    {
        $nodeid = $node->getNodeId();
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($nodeid);
        }

        if (!$deleteBranchOK && $this->hasInteriorNodeWithId($nodeid)) {
            throw new DeleteInteriorNodeException($nodeid);
        }
        if ($deleteBranchOK) {
            // it is critical to use getElements and return an array as opposed to iterating over
            // the actual list.  There is code related to maintaining the parent / child relationships
            // that changes the length of this list as the iteration takes place and the internal pointers
            // get stepped on if you iterate over the actual list instead of a copy.
            $childList = $this->getChildrenOf($node);
            foreach ($childList as $child) {
                $this->deleteNode($child, $deleteBranchOK);
            }
        }

        if ($node === $this->getRoot()) {
            $this->root = null;
        }
        unset($this->nodes[$nodeid]);
        // yes it get destroyed when it falls out of scope, but to be really sure it is not referenced again....
        $node->unsetReferences();
    }

    /**
     * @function getTreeDepthFirst
     * @param TreenodeOrderedInterface|null $startNode
     * @param callable|null $callback
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeDepthFirst(TreenodeOrderedInterface $startNode = null, callable $callback = null): array
    {
        if (is_null($startNode)) {
            $startNode = $this->getRoot();
        } elseif (!$this->hasNode($startNode)) {
            throw new NodeNotInTreeException($startNode->getNodeId());
        }

        if (is_null($callback)) {
            $callback = function (TreenodeOrderedInterface $node) {
                return $node;
            };
        }
        /** @phpstan-ignore-next-line */
        return $this->getTreeDepthFirstRecurse($startNode, $callback);
    }

    /**
     * @function getTreeDepthFirstRecurse
     * @param TreenodeOrderedInterface $startNode
     * @param callable $callable
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeDepthFirstRecurse(TreenodeOrderedInterface $startNode, callable $callable): array
    {
        $result[$startNode->getNodeId()] = $callable($startNode);
        $children = $this->getChildrenOf($startNode);
        foreach ($children as $child) {
            $result = array_merge($result, $this->getTreeDepthFirstRecurse($child, $callable));
        }
        return $result;
    }

    /**
     * @function getTreeBreadthFirst
     * @param TreenodeOrderedInterface|null $startNode
     * @param callable|null $callback
     * @param int|null $levels
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeBreadthFirst(
        TreenodeOrderedInterface $startNode = null,
        callable $callback = null,
        int $levels = null
    ): array {
        if (is_null($startNode)) {
            $startNode = $this->getRoot();
        } elseif (!$this->hasNode($startNode)) {
            throw new NodeNotInTreeException($startNode->getNodeId());
        }

        if (is_null($callback)) {
            $callback = function (TreenodeOrderedInterface $node, $index) {
                return $node;
            };
        }

        return $this->getTreeBreadthFirstRecurse([$startNode], $callback, $levels);
    }

    /**
     * @function getTreeBreadthFirstRecurse
     * @param array $nodes
     * @param callable $callback
     * @param int|null $levels
     * @return array
     */
    protected function getTreeBreadthFirstRecurse(array $nodes, callable $callback, int $levels = null): array
    {
        $result = $nodes;
        array_walk($result, $callback);

        if ($levels === 0) {
            return $result;
        }
        if (!is_null($levels)) {
            $levels--;
        }

        $allChildren = call_user_func_array('array_merge', array_map([$this, 'getChildrenOf'], $nodes));

        if (!empty($allChildren)) {
            return array_merge($result, $this->getTreeBreadthFirstRecurse($allChildren, $callback, $levels));
        } else {
            return $result;
        }
    }

    /**
     * @function getLeaves
     * @return array
     */
    public function getLeaves(): array
    {
        $result = [];
        foreach ($this->nodes as $node) {
            if ($this->hasLeafWithId($node->getNodeId())) {
                $result[] = $node;
            }
        }
        return $result;
    }

    /**
     * @function getInteriorNodes
     * @return array
     */
    public function getInteriorNodes(): array
    {
        $result = [];
        foreach ($this->nodes as $node) {
            if (!$this->hasLeafWithId($node->getNodeId())) {
                $result[] = $node;
            }
        }
        return $result;
    }
}
