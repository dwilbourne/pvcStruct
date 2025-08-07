<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\treesearch\VisitationTrait;

/**
 * nodes are generic.  In order to make them useful, you will need to extend this class to create a specific
 * node type (and extend the tree and treenodefactory classes as well).  Node types typically have a specific
 * kind of payload.
 *
 *  The nodeId property is immutable - the only way to set the nodeId is at hydration.
 *
 *  nodes are allowed to move around
 *  within the same tree, e.g. you can change a node's parent as long as the new parent is in the same tree. It is
 *  important to know that not only does a node keep a reference to its parent, but it also keeps a list of its
 *  children.  So the setParent method is responsible not only for setting the parent property, but it also takes
 *  the parent and adds a node to its child list.
 *
 * @template TreenodeType of TreenodeInterface
 * @implements TreenodeInterface<TreenodeType>
 */
class Treenode implements TreenodeInterface
{
    /**
     * implement NodeVisitableInterface, make Treenodes available for iterable depth first search
     */
    use VisitationTrait;

    /**
     * unique id for this node
     *
     * @var non-negative-int $nodeId
     */
    protected int $nodeId;

    /**
     * @function getNodeId
     * @return non-negative-int
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }

    public function setNodeId(int $nodeId): void
    {
        /**
         * nodeId must be non-negative.
         */
        if ($nodeId < 0) {
            throw new InvalidNodeIdException($nodeId);
        }

        /**
         * nodeId is immutable
         */
        if (isset($this->nodeId)) {
            throw new NodeNotEmptyHydrationException($nodeId);
        }

        $this->nodeId = $nodeId;
    }

    /**
     * @var TreeInterface<TreenodeType>
     */
    protected TreeInterface $tree;

    /**
     * @param  TreeInterface<TreenodeType>  $tree
     *
     * @return void
     * @throws SetTreeException
     */
    public function setTree(TreeInterface $tree): void
    {
        /**
         * $tree property is immutable
         */
        if (isset($this->tree)) {
            throw new SetTreeException($this->nodeId);
        }
        $this->tree = $tree;
    }

    /**
     * reference to parent
     *
     * @var TreenodeType|null
     */
    protected ?TreenodeInterface $parent;

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
        if ($parent) {
            $parentId = $parent->getNodeId();

            /**
             * ensure parent is in the tree
             */
            if ($this->tree->getNode($parentId) === null) {
                throw new InvalidParentNodeIdException($parentId);
            }

            /**
             * ensure we are not creating a circular graph
             */
            if ($parent->isDescendantOf($this)) {
                throw new CircularGraphException($parent->getNodeId());
            }

            /**
             * ensure we are not trying to move the root node
             */
            if ($this->tree->getRoot() === $this) {
                throw new RootCannotBeMovedException();
            }

            /**
             * if parent is not null, add this node to the parent's child collection
             */
            $childCollection = $parent->getChildren();
            $childCollection->add($this->getNodeId(), $this);
        }

        /**
         * set the parent.  If the parent is null, the tree will handle setting it
         * as the root of the tree
         */
        $this->parent = $parent;
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
     * @var TreenodeChildCollectionInterface<TreenodeType> $children
     */
    protected TreenodeChildCollectionInterface $children;

    /**
     * @return TreenodeChildCollectionInterface<TreenodeType>
     */
    public function getChildren(): TreenodeChildCollectionInterface
    {
        return $this->children;
    }

    /**
     * @var non-negative-int
     */
    protected int $index;

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * @param  TreenodeChildCollectionFactoryInterface<TreenodeType>  $collectionFactory
     */
    public function __construct(protected TreenodeChildCollectionFactoryInterface $collectionFactory)
    {
        $this->children = $this->collectionFactory->makeChildCollection();
    }

    /**
     * methods describing the nature of the node
     */

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

    public function isRoot(): bool
    {
        return $this->tree->getRoot() === $this;
    }


    /**
     * @return TreenodeType|null
     */
    public function getFirstChild(): ?TreenodeInterface
    {
        return $this->getChildren()->getFirst();
    }

    /**
     * @return TreenodeType|null
     */
    public function getLastChild(): ?TreenodeInterface
    {
        return $this->getChildren()->getLast();
    }

    /**
     * @param  non-negative-int  $n
     *
     * @return TreenodeType|null
     */
    public function getNthChild(int $n): ?TreenodeInterface
    {
        return $this->getChildren()->getNth($n);
    }

    /**
     * getChildrenArray
     *
     * @return array<non-negative-int, TreenodeType>
     */
    public function getChildrenArray(): array
    {
        return $this->getChildren()->getElements();
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
     * @param  non-negative-int  $nodeId
     *
     * @return TreenodeType|null
     */
    public function getChild(int $nodeId): ?TreenodeInterface
    {
        return $this->children->getElement($nodeId);
    }

    /**
     * getSiblings returns a collection of this node's siblings
     *
     * @return TreenodeChildCollectionInterface<TreenodeType>
     */
    public function getSiblings(): TreenodeChildCollectionInterface
    {
        /**
         * the root has no parent, so there is no existing child collection to get from a parent.
         * Not sure why phpstan needs the type hinting.......
         */
        if ($this->isRoot()) {
            /** @var TreenodeChildCollection<TreenodeType> $collection */
            $collection = $this->collectionFactory->makeChildCollection();
            $collection->add($this->getNodeId(), $this);
        } else {
            $parent = $this->getParent();
            assert(!is_null($parent));
            /** @var TreenodeChildCollection<TreenodeType> $collection */
            $collection = $parent->getChildren();
        }
        return $collection;
    }
}
