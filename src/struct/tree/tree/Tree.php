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
use pvc\struct\tree\iface\node\TreenodeInterface;
use pvc\struct\tree\iface\tree\TreeInterface;
use pvc\struct\tree\node\Treenode;

/**
 * Class Tree
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 */
class Tree implements TreeInterface
{

    /**
     * @var int
     */
    protected int $treeid;

    /**
     * @var TreenodeInterface|null
     */
    protected ?TreenodeInterface $root;

    /**
     * @var array[int|string]mixed
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
     * @return TreenodeInterface|null
     */
    public function getRoot(): ?TreenodeInterface
    {
        return $this->root;
    }

    /**
     * @function setRoot
     * @param TreenodeInterface $node
     * @throws AlreadySetRootException
     */
    protected function setRoot(TreenodeInterface $node) : void
    {
        if (isset($this->root)) {
            throw new AlreadySetRootException();
        }
        $this->root = $node;
    }

    /**
     * @function hasNode
     * @param TreenodeInterface $node
     * @return bool
     */
    public function hasNode(TreenodeInterface $node): bool
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
     * @return TreenodeInterface|null
     */
    public function getNode(int $nodeid): ?TreenodeInterface
    {
        if (!isset($this->nodes[$nodeid])) {
            return null;
        }
        return $this->nodes[$nodeid];
    }

    /**
     * @function getNodes
     * @return TreenodeInterface[]
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
            if (!$node instanceof TreenodeInterface) {
                throw new InvalidNodeDataException();
            }
            $nodeArray[$node->getNodeId()] = $node;
        }
        foreach ($nodeArray as $node) {
            $this->addNodeHelper($node, $nodeArray);
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
     * @param TreenodeInterface $parent
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getChildrenOf(TreenodeInterface $parent): array
    {
        if (!$this->hasNode($parent)) {
            throw new NodeNotInTreeException($parent->getParentId());
        }
        $result = [];
        foreach ($this->nodes as $node) {
            if ($parent->getNodeId() === $node->getParentid()) {
                $result[$node->getNodeId()] = $node;
            }
        }
        return $result;
    }

    /**
     * @function getParentOf
     * @param TreenodeInterface $node
     * @return TreenodeInterface|null
     * @throws NodeNotInTreeException
     */
    public function getParentOf(TreenodeInterface $node): ?TreenodeInterface
    {
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($node->getNodeId());
        }
        if ($node === $this->getRoot()) {
            return null;
        }
        return $this->nodes[$node->getParentId()];
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

        $result = true;
        foreach ($this->nodes as $node) {
            if ($node->getParentId() === $nodeid) {
                $result = false;
                break;
            }
        }
        return $result;
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
     * @function addNode
     * @param TreenodeInterface $node
     * @throws AlreadySetNodeidException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     * @throws NodeNotInTreeException
     */
    public function addNode(TreenodeInterface $node): void
    {
        $this->addNodeHelper($node, $this->nodes);
    }

    /**
     * this routine is not only used by addNode but also by hydrate.  The difference is that when being
     * called by addNode, the nodeArray argument is $this->nodes, whereas when being called by hydrate,
     * $nodeArray is the array of nodes being imported.  This allows us to hydrate the tree having the nodes
     * in any arbitrary order instead of forcing that we add the root first and then add everything in a
     * breadth-first fashion to insure that all parent references are valid.
     *
     * @function addNodeHelper
     * @param TreenodeInterface $node
     * @param array $nodeArray
     * @throws AlreadySetNodeidException
     * @throws AlreadySetRootException
     * @throws CircularGraphException
     * @throws InvalidParentNodeException
     * @throws InvalidTreeidException
     */
    protected function addNodeHelper(TreenodeInterface $node, array $nodeArray) : void
    {
        $nodeid = $node->getNodeId();
        if ($this->hasNode($node)) {
            throw new AlreadySetNodeidException($nodeid);
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
     * @param TreenodeInterface $node
     * @param array $nodeArray
     * @throws CircularGraphException
     */
    protected function checkCircularity($node, array $nodeArray) : void
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
     * @param Treenode $node
     * @param bool $deleteBranchOK
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode($node, bool $deleteBranchOK = false): void
    {
        $nodeid = $node->getNodeId();
        if (!$this->hasNode($node)) {
            throw new NodeNotInTreeException($nodeid);
        }

        if (!$deleteBranchOK && $this->hasInteriorNodeWithId($node->getNodeId())) {
            throw new DeleteInteriorNodeException($nodeid);
        }
        if ($deleteBranchOK) {
            $children = $this->getChildrenOf($node);
            foreach ($children as $child) {
                $this->deleteNode($child, $deleteBranchOK);
            }
        }

        if ($node === $this->getRoot()) {
            $this->root = null;
        }
        unset($this->nodes[$nodeid]);
        $node->unsetReferences();
    }

    /**
     * @function getTreeDepthFirst
     * @param TreenodeInterface|null $startNode
     * @param callable|null $callback
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeDepthFirst(TreenodeInterface $startNode = null, callable $callback = null): array
    {
        if (is_null($callback)) {
            $callback = function (TreenodeInterface $node) {
                return $node;
            };
        }

        if (is_null($startNode)) {
            $startNode = $this->getRoot();
        } elseif (!$this->hasNode($startNode)) {
            throw new NodeNotInTreeException($startNode->getNodeId());
        }
        /** @phpstan-ignore-next-line */
        return $this->getTreeDepthFirstRecurse($startNode, $callback);
    }

    /**
     * @function getTreeDepthFirstRecurse
     * @param TreenodeInterface $startNode
     * @param callable $callable
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeDepthFirstRecurse(TreenodeInterface $startNode, callable $callable): array
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
     * @param TreenodeInterface|null $startNode
     * @param callable|null $callback
     * @param int|null $levels
     * @return array
     * @throws NodeNotInTreeException
     */
    public function getTreeBreadthFirst(
        TreenodeInterface $startNode = null,
        callable $callback = null,
        int $levels = null
    ): array {
        if (is_null($startNode)) {
            $startNode = $this->getRoot();
        } elseif (!$this->hasNode($startNode)) {
            throw new NodeNotInTreeException($startNode->getNodeId());
        }

        if (is_null($callback)) {
            $callback = function (TreenodeInterface $node) {
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
            if ($this->hasLeafWithId($node->getNodeid())) {
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
            if (!$this->hasLeafWithId($node->getNodeid())) {
                $result[] = $node;
            }
        }
        return $result;
    }
}
