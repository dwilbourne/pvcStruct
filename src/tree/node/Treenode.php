<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\treesearch\VisitationTrait;

/**
 *  The nodeid property is immutable - the only way to set the nodeid is at hydration.  The same applies to the treeId property,
 *  which is set at construction time.
 *
 *  The same is almost true for the parent property, but the difference is that the nodes are allowed to move around
 *  within the same tree, e.g. you can change a node's parent as long as the new parent is in the same tree. It is
 *  important to know that not only does a node keep a reference to its parent, but it also keeps a list of its
 *  children.  So the setParent method is responsible not only for setting the parent property, but it also takes
 *  the parent and adds a node to its child list.
 *
 * @template TreenodeType of TreenodeInterface
 * @template CollectionType of CollectionInterface
 * @template TreeType of TreeInterface
 * @implements TreenodeInterface<TreenodeType, CollectionType, TreeType>
 */
class Treenode implements TreenodeInterface
{
    /**
     * implement NodeVisitableInterface, make Treenodes available for iterable depth first search
     */
    use VisitationTrait;

    /**
     * @var CollectionType $children
     */
    public CollectionInterface $children;
    /**
     * unique id for this node
     *
     * @var non-negative-int $nodeid
     */
    protected int $nodeid;
    /**
     * @var non-negative-int|null
     */
    protected ?int $parentId = null;
    /**
     * @var non-negative-int
     */
    protected int $treeId;
    /**
     * reference to parent
     *
     * @var TreenodeType|null
     */
    protected ?TreenodeInterface $parent;
    /**
     * reference to containing tree
     *
     * @var TreeType
     */
    protected TreeInterface $tree;

    /**
     * @param  CollectionType  $collection
     *
     * @throws ChildCollectionException
     */
    public function __construct(CollectionInterface $collection)
    {
        /**
         * set the child collection
         */
        if (!$collection->isEmpty()) {
            throw new ChildCollectionException();
        } else {
            $this->children = $collection;
        }
    }

    /**
     * isEmpty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->nodeid ?? null);
    }

    /**
     * @param  TreenodeDtoInterface  $dto
     *
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeIdException
     * @throws InvalidTreeidException
     */
    public function hydrate(TreenodeDtoInterface $dto): void
    {
        /**
         * cannot hydrate a node if it already has been hydrated
         */
        if (!$this->isEmpty()) {
            throw new NodeNotEmptyHydrationException($this->getNodeId());
        }

        /**
         * set the nodeId, $parentId and $treeId
         */
        $this->setNodeId($dto->getNodeId());
        $this->setParentId($dto->getParentId());
        $this->setTreeId($dto->getTreeId());
    }

    /**
     * @function getNodeId
     * @return non-negative-int
     */
    public function getNodeId(): int
    {
        return $this->nodeid;
    }

    protected function setNodeId(int $nodeId): void
    {
        /**
         * nodeid must be non-negative.
         */
        if ($nodeId < 0) {
            throw new InvalidNodeIdException($nodeId);
        }

        $this->nodeid = $nodeId;
    }

    protected function setParentId(?int $parentId): void
    {
        if (($parentId !== null) && ($parentId < 0)) {
            throw new InvalidParentNodeIdException($parentId);
        }
        $this->parentId = $parentId;
    }

    protected function setTreeId(?int $treeId): void
    {
        if ($treeId !== null) {
            if ($treeId < 0) {
                throw new InvalidTreeidException($treeId);
            }
            $this->treeId = $treeId;
        }
    }

    public function getIndex(): ?int
    {
        return null;
    }

    /**
     * @return TreenodeType|null
     */
    public function getFirstChild()
    {
        /** @var CollectionType<TreenodeType> $children */
        $children = $this->getChildren();
        return $children->getFirst();
    }

    /**
     * @return TreenodeType|null
     */
    public function getLastChild()
    {
        /** @var CollectionType<TreenodeType> $children */
        $children = $this->getChildren();
        return $children->getLast();
    }

    /**
     * @param  non-negative-int  $n
     *
     * @return TreenodeType|null
     */
    public function getNthChild(int $n)
    {
        /** @var CollectionType<TreenodeType> $children */
        $children = $this->getChildren();
        return $children->getNth($n);
    }

    /**
     * getChildrenAsArray
     *
     * @return array<TreenodeType>
     */
    public function getChildrenArray(): array
    {
        /** @var array<TreenodeType> $result */
        $result = $this->getChildren()->getElements();
        return $result;
    }

    /**
     * @function hasChildren
     * @return bool
     */
    public function hasChildren(): bool
    {
        return (!$this->children->isEmpty());
    }

    /**
     * @function getChild
     *
     * @param  non-negative-int  $nodeid
     *
     * @return TreenodeType|null
     */
    public function getChild(int $nodeid)
    {
        /** @var TreenodeType $child */
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
    public function getSiblings(): CollectionInterface
    {
        /**
         * the root has no parent, so there is no existing child collection to get from a parent. We do have to go a
         * long way to get to the collection factory so we can make a collection and add this node to it.
         */

        if ($this->getTree()->rootTest($this)) {
            /** @var CollectionType $collection */
            $collection = $this->getTree()->makeCollection();
            $collection->add($this->getNodeId(), $this);
        } else {
            $parent = $this->getParent();
            assert(!is_null($parent));
            /** @var CollectionType $collection */
            $collection = $parent->getChildren();
        }
        return $collection;
    }

    /**
     * @function getTree
     * @return TreeType
     */
    public function getTree(): TreeInterface
    {
        return $this->tree;
    }

    /**
     * @param  TreeType  $tree
     *
     * @return void
     * @throws SetTreeException
     */
    public function setTree(TreeInterface $tree): void
    {
        /**
         * tree property is immutable
         */
        if (isset($this->tree)) {
            throw new SetTreeException($this->getNodeId());
        }
        /**
         * if this node was hydrated from a dto, the treeId property might not
         * be set.  if it is not, then adopt the treeId from the tree.
         */
        if (!isset($this->treeId)) {
            $this->treeId = $tree->getTreeId();
        }

        /**
         * ensure the treeId from the node matches the one from the tree
         */
        if ($this->treeId != $tree->getTreeId()) {
            throw new SetTreeException($this->getNodeId());
        }

        /**
         * set the reference
         */
        $this->tree = $tree;
    }

    /**
     * @function getParent
     * @return TreenodeType|null
     * the parent reference is a convenience, a shortcut because we could
     * always go to the tree and get the parent from the tree via the
     * node's parentId property.  In a large tree, this reference could save
     * a few cpu cycles....
     */
    public function getParent()
    {
        return $this->parent ?? null;
    }

    /**
     * @param ?TreenodeType  $parent
     *
     * @return void
     *
     * two cases:  the first is this is called as part of this node being added
     * to the tree.  In this case, the parent property is currently null.
     *
     * The second case is when you are trying to move this node within the tree.
     * In this case, the parent is already set and the argument is intended
     * to be the new parent.
     */
    public function setParent(?TreenodeInterface $parent): void
    {
        /**
         * if parent is null, see if a parent node can be determined from the parentId
         * property via the tree.  If not, throw an exception
         */
        if ($parent === null && $this->parentId !== null) {
            /** @var TreenodeType|null $parent */
            $parent = $this->tree->getNode($this->parentId);
            if (!$parent) {
                throw new InvalidParentNodeIdException($this->parentId);
            }
        }

        /**
         * if parent is not null, ensure parent is in the tree.  phpstan
         * does not quite process this correctly unless it is written in a
         * clumsy fashion with the typehint which appears redundant
         */
        if ($parent) {
            /** @var non-negative-int $parentId */
            $parentId = $parent->getNodeId();
            if ($this->tree->getNode($parentId) === null) {
                throw new InvalidParentNodeIdException($parentId);
            }
        }

        /**
         * ensure we are not creating a circular graph
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
            $childCollection->add($this->getNodeId(), $this);
        }

        /**
         * set the parent and the parentId
         */
        $this->parent = $parent;
        $this->setParentId($parent?->getNodeId());
    }

    /**
     * @function isDescendantOf
     *
     * @param  TreenodeType  $node
     *
     * @return bool
     */
    public function isDescendantOf(TreenodeInterface $node): bool
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
     * @return CollectionType
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return ($this->tree->getRoot() === $this);
    }

    /**
     * @function isAncestorOf
     *
     * @param  TreenodeType  $node
     *
     * @return bool
     */
    public function isAncestorOf(TreenodeInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }

    /**
     * @function isLeaf
     * @return bool
     */
    public function isLeaf(): bool
    {
        return ($this->children->isEmpty());
    }
}
