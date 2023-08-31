<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\payload\PayloadTrait;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;

/**
 * @template ValueType
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements TreenodeAbstractInterface<ValueType, NodeType, TreeType, CollectionType>
 */
class TreenodeAbstract implements TreenodeAbstractInterface
{
    use PayloadTrait;

    /**
     * unique id for this node
     * @var non-negative-int $nodeid
     */
    protected int $nodeid;

    /**
     * reference to parent
     * @var NodeType|null
     */
    protected ?TreenodeAbstractInterface $parent;

    /**
     * reference to containing tree
     * @var TreeType
     */
    protected TreeAbstractInterface $tree;

    /**
     * @var CollectionType $children
     */
    protected CollectionAbstractInterface $children;

    /**
     * @var non-negative-int
     */
    protected int $visitCount = 0;

    /**
     * @param non-negative-int $nodeid
     * @param ?non-negative-int $parentId
     * @param non-negative-int $treeId
     * @param TreeType $tree
     * @param CollectionType $collection
     * @throws InvalidNodeIdException
     */
    public function __construct(
        int $nodeid,
        ?int $parentId,
        int $treeId,
        TreeAbstractInterface $tree,
        CollectionAbstractInterface $collection
    ) {
        /**
         * nodeid must be non-negative.  phpstan will catch static problems but to be thorough, let's catch it anyway
         */
        if ($nodeid < 0) {
            throw new InvalidNodeIdException($nodeid);
        }

        /**
         * node id cannot already exist in the tree
         */
        if ($tree->getNode($nodeid)) {
            throw new AlreadySetNodeidException($nodeid);
        }
        $this->nodeid = $nodeid;

        /**
         * verify that the tree id in the arguments matches the tree id of the tree we are setting a reference to
         */
        if ($treeId != $tree->getTreeId()) {
            throw new SetTreeIdException();
        }

        /**
         * tree reference in this structure must be set before calling setParent so that we ensure $parent is
         * already in the same tree
         */
        $this->tree = $tree;

        /**
         * set the child collection
         */
        if (!$collection->isEmpty()) {
            throw new ChildCollectionException();
        } else {
            $this->children = $collection;
        }

        /**
         * set the value validator to a default
         */
        $this->setValueValidator(new TreenodeValueValidatorDefault());

        /**
         * setParent also sets the "reciprocal pointer" by adding this node to the child collection of the parent
         */
        $this->setParent($parentId);
    }

    /**
     * @function setParent
     *
     * @param non-negative-int|null $parentId
     */
    public function setParent(?int $parentId): void
    {
        if (!is_null($parentId)) {
            /** @var NodeType $parent */
            $parent = $this->getTree()->getNode($parentId);
            if (is_null($parent)) {
                /**
                 * parent id is not null but there is no existing node in the tree with node id == parent id
                 */
                throw new InvalidParentNodeException($parentId);
            }
        } else {
            /**
             * parent id is null so parent is also null - it thinks it is the root node.  It is up to the containing
             * tree to decide whether it really is or is not. See the setRoot method in TreeAbstract
             */
            $parent = null;
        }

        /**
         * make sure we are not creating a circular graph
         */
        if ($parent && $parent->isDescendantOf($this)) {
            throw new CircularGraphException($parent->getNodeId());
        }

        /**
         * setParent is not just for construction - it is used to move nodes around in the tree as well.  If this
         * node is the root node, then it cannot be moved in the tree
         */
        if ($this->tree->getRoot()?->getNodeId() === $this->getNodeId()) {
            throw new RootCannotBeMovedException();
        }

        /**
         * if parent is not null, add this node to the parent's child collection
         */
        if ($childCollection = $parent?->getChildren()) {
            $childCollection->push($this);
        }

        /**
         * set the parent
         */
        $this->parent = $parent;
    }

    /**
     * @function getNodeId
     * @return non-negative-int
     */
    public function getNodeId(): int
    {
        return $this->nodeid;
    }

    /**
     * @function getParentId
     * @return non-negative-int|null
     */
    public function getParentId(): ?int
    {
        return ($parent = $this->getParent()) ? $parent->getNodeId() : null;
    }

    /**
     * @function getParent
     * @return NodeType|null
     */
    public function getParent(): ?TreenodeAbstractInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getTree
     * @return TreeType
     */
    public function getTree(): TreeAbstractInterface
    {
        return $this->tree;
    }

    /**
     * getTreeId
     * @return non-negative-int
     */
    public function getTreeId(): int
    {
        return $this->getTree()->getTreeId();
    }

    /**
     * @function getChildren
     * @return CollectionType
     */
    public function getChildren(): CollectionAbstractInterface
    {
        return $this->children;
    }

    /**
     * @function isLeaf
     * @return bool
     */
    public function isLeaf(): bool
    {
        return ($this->getChildren()->isEmpty());
    }

    /**
     * @function isInteriorNode
     * @return bool
     */
    public function isInteriorNode(): bool
    {
        return (!$this->getChildren()->isEmpty());
    }

    /**
     * @function getChild
     * @param non-negative-int $nodeid
     * @return NodeType|null
     */
    public function getChild(int $nodeid): ?TreenodeAbstractInterface
    {
        /** @var NodeType $child */
        foreach ($this->getChildren() as $child) {
            if ($nodeid == $child->getNodeId()) {
                return $child;
            }
        }
        return null;
    }

    /**
     * getSiblings returns a collection of this node's siblings
     *
     * @return CollectionType
     */
    public function getSiblings(): CollectionAbstractInterface
    {
        if ($this->getTree()->rootTest($this)) {
            /** @var CollectionType $collection */
            $collection = $this->tree->makeCollection();
            $collection->push($this);
        } else {
            /** @var NodeType $parent */
            $parent = $this->getParent();
            /** @var CollectionType $collection */
            $collection = $parent->getChildren();
        }
        return $collection;
    }

    /**
     * @function isAncestorOf
     * @param NodeType $node
     * @return bool
     */
    public function isAncestorOf(TreenodeAbstractInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }

    /**
     * @function isDescendantOf
     * @param NodeType $node
     * @return bool
     */
    public function isDescendantOf(TreenodeAbstractInterface $node): bool
    {
        if ($this->getParent() === $node) {
            return true;
        }
        if (is_null($this->getParent())) {
            return false;
        } else {
            return $this->getParent()->isDescendantOf($node);
        }
    }

    /**
     * getVisitCount
     * @return non-negative-int
     */
    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    /**
     * addVisit
     */
    public function addVisit(): void
    {
        $this->visitCount++;
    }

    /**
     * clearVisitCount
     */
    public function clearVisitCount(): void
    {
        $this->visitCount = 0;
    }
}
