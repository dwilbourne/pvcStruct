<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeIdException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeException;
use pvc\struct\tree\err\NodeNotInitializedException;
use pvc\struct\treesearch\VisitationTrait;

/**
 * nodes are generic.  In order to make them useful, you will need to extend this class to create a specific
 * node type (and extend the tree class as well).  Node types typically have a specific
 * kind of 'payload'.  You will also need to implement factories and collection types.  See
 * the example for guidance.
 *
 * The nodeId property is immutable - the only way to set the nodeId is at hydration.  NodeIds are
 * typed as array-keys in this class
 *
 * nodes are allowed to move around
 * within the same tree, e.g. you can change a node's parent as long as the new parent is in the same tree. It is
 * important to know that not only does a node keep a reference to its parent, but it also keeps a list of its
 * children.  So the setParent method is responsible not only for setting the parent property, but it also takes
 * the parent and adds a node to its child list.
 *
 * Nodes cannot move between trees, so the tree property is immutable also.
 *
 * @template NodeId of array-key
 * @template TreeType of TreeInterface
 * @template NodeType of TreenodeInterface
 * @template CollectionType of CollectionInterface<NodeId, NodeType>
 * @implements TreenodeInterface<NodeId, TreeType, NodeType, CollectionType>
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
     * @var NodeId $nodeId
     */
    protected $nodeId;

    /**
     * getNodeId
     * @return NodeId
     */
    public function getNodeId()
    {
        if ($this->nodeId === null) {
            throw new NodeNotInitializedException();
        }
        return $this->nodeId;
    }
    
    /**
     * setNodeId
     * @param NodeId $nodeId
     *
     * @return void
     */
    public function setNodeId($nodeId): void
    {
        if (!$this->nodeIdTester->testValue($nodeId)) {
            throw new InvalidNodeIdException();
        }

        /**
         * nodeId is immutable
         */
        if ($this->nodeId !== null) {
            throw new NodeNotEmptyHydrationException($nodeId);
        }
        $this->nodeId = $nodeId;
    }

    /**
     * @var TreeType
     */
    protected $tree;

    /**
     * @param  TreeType  $tree
     *
     * @return void
     * @throws SetTreeException
     */
    public function setTree($tree): void
    {
        /**
         * $tree property is immutable
         */
        if ($this->tree !== null) {
            throw new SetTreeException($this->nodeId);
        }
        $this->tree = $tree;
    }

    /**
     * reference to parent
     *
     * @var NodeType|null
     */
    protected ?TreenodeInterface $parent;

    /**
     * @param ?NodeType  $parent
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
    public function setParent($parent): void
    {
        if ($parent) {
            /** @var NodeId $parentId */
            $parentId = $parent->getNodeId();

            /**
             * ensure parent is in the tree
             */
            if ($this->tree->getNode($parentId) === null) {
                throw new InvalidParentNodeIdException((string) $parentId);
            }

            /**
             * ensure we are not creating a circular graph
             */
            if ($parent->isDescendantOf($this)) {
                throw new CircularGraphException((string) $parentId);
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
            $childCollection->add($this, $this->getNodeId());
        }

        /**
         * set the parent.  If the parent is null, the tree will handle setting it
         * as the root of the tree
         */
        $this->parent = $parent;
    }

    /**
     * @function getParent
     * @return NodeType|null
     */
    public function getParent(): ?TreenodeInterface
    {
        return $this->parent ?? null;
    }


    /**
     * @var CollectionType $children
     */
    protected $children;

    /**
     * @return CollectionType
     */
    public function getChildren()
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
     * @param  ValTesterInterface<mixed>  $nodeIdTester
     * @param  TreenodeCollectionFactoryInterface<CollectionType>  $collectionFactory
     */
    public function __construct(
        protected ValTesterInterface $nodeIdTester,
        protected TreenodeCollectionFactoryInterface $collectionFactory)
    {
        $this->children = $this->collectionFactory->makeCollection();
    }

    /**
     * methods describing the nature of the node
     */

    /**
     * @function isDescendantOf
     *
     * @param  NodeType  $node
     *
     * @return bool
     */
    public function isDescendantOf($node): bool
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
     * @param  NodeType  $node
     *
     * @return bool
     */
    public function isAncestorOf($node): bool
    {
        return $node->isDescendantOf($this);
    }

    public function isRoot(): bool
    {
        return $this->tree->getRoot() === $this;
    }


    /**
     * @return NodeType|null
     */
    public function getFirstChild()
    {
        return $this->getChildren()->getFirst();
    }

    /**
     * @return NodeType|null
     */
    public function getLastChild()
    {
        return $this->getChildren()->getLast();
    }

    /**
     * @param  non-negative-int  $n
     *
     * @return NodeType|null
     */
    public function getNthChild(int $n)
    {
        return $this->getChildren()->getNth($n);
    }

    /**
     * getChildrenArray
     *
     * @return array<NodeType>
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
     * @param  NodeId  $nodeId
     *
     * @return NodeType|null
     */
    public function getChild($nodeId)
    {
        return $this->children->getElement($nodeId);
    }

    /**
     * getSiblings returns a collection of this node's siblings
     *
     * @return CollectionType
     */
    public function getSiblings()
    {
        /**
         * the root has no parent, so there is no existing child collection to get from a parent.
         * Not sure why phpstan needs the type hinting.......
         */
        if ($this->isRoot()) {
            /** @var CollectionType $collection */
            $collection = $this->collectionFactory->makeCollection();
            $collection->add($this, $this->getNodeId());
        } else {
            $parent = $this->getParent();
            assert(!is_null($parent));
            /** @var CollectionType $collection */
            $collection = $parent->getChildren();
        }
        return $collection;
    }

}
