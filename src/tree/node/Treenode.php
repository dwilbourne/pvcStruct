<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\payload\PayloadTrait;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidValueException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\treesearch\VisitationTrait;

/**
 *  The nodeid property is immutable - the only way to set the nodeid is at hydration.  The same applies to the tree property,
 *  which is set at construction time.
 *
 *  This means that there are no setters for these properties.  Together, these two design points ensure that nodes
 *  cannot be hydrated except in the context of belonging to a tree.  That in turn makes it a bit easier to enforce the
 *  design point that all nodeids in a tree must be unique.
 *
 *  The same is almost true for the parent property, but the difference is that the nodes are allowed to move around
 *  within the same tree, e.g. you can change a node's parent as long as the new parent is in the same tree. It is
 *  important to know that not only does a node keep a reference to its parent, but it also keeps a list of its
 *  children.  So the setParent method is responsible not only for setting the parent property, but it also takes
 *  the parent and adds a node to its child list.
 *
 * @template TreenodeType of TreenodeInterface
 * @template CollectionType of CollectionInterface
 * @implements TreenodeInterface<TreenodeType, CollectionType>
 * @phpstan-import-type TreenodeDtoShape from TreenodeInterface
 */
class Treenode implements TreenodeInterface
{
    /**
     * implement NodeVisitableInterface, make Treenodes available for iterable depth first search
     */
    use VisitationTrait;

    /**
     * unique id for this node
     * @var non-negative-int $nodeid
     */
    protected int $nodeid;

    /**
     * reference to parent
     * @var TreenodeType|null
     */
    protected ?TreenodeInterface $parent;

    /**
     * reference to containing tree
     * @var TreeInterface<TreenodeType, CollectionType>
     */
    protected TreeInterface $tree;

    /**
     * @var CollectionType $children
     */
    public CollectionInterface $children;

    /**
     * @param CollectionType $collection
     * @param TreeInterface<TreenodeType, CollectionType> $tree
     * @throws ChildCollectionException
     */
    public function __construct(CollectionInterface $collection, TreeInterface $tree)
    {
        /**
         * set the child collection
         */
        if (!$collection->isEmpty()) {
            throw new ChildCollectionException();
        } else {
            $this->children = $collection;
        }

        $this->tree = $tree;
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->nodeid ?? null);
    }

    /**
     * @param TreenodeDtoShape $dto
     * @return void
     * @throws AlreadySetNodeidException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @throws NodeNotEmptyHydrationException
     * @throws RootCannotBeMovedException
     * @throws SetTreeIdException
     * @throws InvalidValueException
     */
    public function hydrate($dto): void
    {
        /**
         * cannot hydrate a node if it already has been hydrated
         */
        if (!$this->isEmpty()) {
            throw new NodeNotEmptyHydrationException($this->getNodeId());
        }

        /**
         * set the nodeId
         */
        $this->setNodeId($dto->nodeId);

        /**
         * recall that the node is constructed such that the reference to its containing tree is already set.
         * If the treeId of the dto is not null, verify that the dto tree id matches the tree id of the containing tree.
         */
        if (!is_null($dto->treeId) && ($dto->treeId != $this->getTreeId())) {
            throw new SetTreeIdException();
        }

        /**
         * setParent also sets the "reciprocal pointer" by adding this node to the child collection of the parent.
         * We have to set the index first (if it is in the dto) because in the case of ordered collections,
         * the collection will need to know what index to use when adding this node to the child collection of the
         * parent.
         */
        $this->setParent($dto->parentId);
    }

    protected function setNodeId(int $nodeId): void
    {
        /**
         * nodeid must be non-negative.
         */
        if ($nodeId < 0) {
            throw new InvalidNodeIdException($nodeId);
        }

        /**
         * node id cannot already exist in the tree
         */
        if ($this->getTree()->getNode($nodeId)) {
            throw new AlreadySetNodeidException($nodeId);
        }
        $this->nodeid = $nodeId;
    }

    /**
     * @function setParent
     * @param non-negative-int|null $parentId
     */
    public function setParent(?int $parentId): void
    {
        if (!is_null($parentId)) {
            /**
             * @var TreenodeType|null $parent
             */
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
             * tree to decide whether it really is or is not. See the setRoot method in Tree
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
            $childCollection->add($this->getNodeId(), $this);
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
        return $this->getParent()?->getNodeId();
    }

    /**
     * @function getParent
     * @return TreenodeType|null
     */
    public function getParent(): ?TreenodeInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getTree
     * @return TreeInterface<TreenodeType, CollectionType>
     */
    public function getTree(): TreeInterface
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
     * @return CollectionType
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }

    /**
     * getChildrenAsArray
     * @return array<TreenodeType>
     */
    public function getChildrenArray(): array
    {
        /** @var array<TreenodeType> $result*/
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
     * @param non-negative-int $nodeid
     * @return TreenodeType|null
     */
    public function getChild(int $nodeid): ?TreenodeInterface
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

        /** @phpstan-ignore-next-line */
        if ($this->getTree()->rootTest($this)) {
            /** @var CollectionType $collection */
            $collection = $this->getTree()->getTreenodeFactory()->getTreenodeCollectionFactory()->makeCollection([]);
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
     * @return bool
     */
    public function isRoot(): bool
    {
        return ($this->tree->getRoot() === $this);
    }

    /**
     * @function isAncestorOf
     * @param TreenodeType $node
     * @return bool
     */
    public function isAncestorOf(TreenodeInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }

    /**
     * @function isDescendantOf
     * @param TreenodeType $node
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
     * @function isLeaf
     * @return bool
     */
    public function isLeaf(): bool
    {
        return ($this->children->isEmpty());
    }
}
