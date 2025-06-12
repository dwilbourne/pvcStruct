<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\dto\DtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
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
 * @template PayloadType
 * @implements TreenodeInterface<PayloadType>
 * @phpstan-import-type TreenodeDtoShape from TreenodeInterface
 */
class Treenode implements TreenodeInterface
{
    /**
     * @use PayloadTrait<PayloadType>
     */
    use PayloadTrait;

    /**
     * make Treenodes available for iterable depth first search
     */
    use VisitationTrait;

    /**
     * unique id for this node
     * @var non-negative-int $nodeid
     */
    protected int $nodeid;

    /**
     * reference to parent
     * @var TreenodeInterface<PayloadType>|null
     */
    protected ?TreenodeInterface $parent;

    /**
     * reference to containing tree
     * @var TreeInterface<PayloadType>
     */
    protected TreeInterface $tree;

    /**
     * @var non-negative-int
     */
    protected int $index;

    /**
     * @var TreenodeCollectionInterface<PayloadType> $children
     */
    public TreenodeCollectionInterface $children;

    /**
     * @param TreenodeCollectionInterface<PayloadType> $collection
     * @param TreeInterface<PayloadType> $tree
     * @param ?ValTesterInterface<PayloadType> $payloadTester
     * @throws ChildCollectionException
     */
    public function __construct(TreenodeCollectionInterface $collection, TreeInterface $tree, ?ValTesterInterface $payloadTester = null)
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

        /**
         * set the tester if it was included
         */
        if ($payloadTester) {
            $this->setPayloadTester($payloadTester);
        }
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
     * @param TreenodeDtoShape&DtoInterface $dto
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
    public function hydrate(DtoInterface $dto): void
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
        if (isset($dto->index)) $this->setIndex($dto->index);
        $this->setParent($dto->parentId);

        /**
         * now set the payload
         */
        $this->setPayload($dto->payload);
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
             * @var TreenodeInterface<PayloadType>|null $parent
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
     * @return TreenodeInterface<PayloadType>|null
     */
    public function getParent(): ?TreenodeInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getTree
     * @return TreeInterface<PayloadType>
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
     * @return TreenodeCollectionInterface<PayloadType>
     */
    public function getChildren(): TreenodeCollectionInterface
    {
        return $this->children;
    }

    /**
     * getChildrenAsArray
     * @return array<TreenodeInterface<PayloadType>>
     */
    public function getChildrenArray(): array
    {
        return $this->children->getElements();
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
     * @return TreenodeInterface<PayloadType>|null
     */
    public function getChild(int $nodeid): ?TreenodeInterface
    {
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
     * @return TreenodeCollectionInterface<PayloadType>
     */
    public function getSiblings(): TreenodeCollectionInterface
    {
        /**
         * the root has no parent, so there is no existing child collection to get from a parent. We do have to go a
         * long way to get to the collection factory so we can make a collection and add this node to it.
         */
        if ($this->getTree()->rootTest($this)) {
            /** @var TreenodeCollection<PayloadType> $collection */
            $collection = $this->getTree()->getTreenodeFactory()->getTreenodeCollectionFactory()->makeTreenodeCollection();
            $collection->add($this->getNodeId(), $this);
        } else {
            $parent = $this->getParent();
            assert(!is_null($parent));
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
     * @param TreenodeInterface<PayloadType> $node
     * @return bool
     */
    public function isAncestorOf(TreenodeInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }

    /**
     * @function isDescendantOf
     * @param TreenodeInterface<PayloadType> $node
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

    /**
     * @function getIndex returns the ordinal position of this node in its list of siblings
     * @return non-negative-int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * sets this node's index property.  This method should never be called
     * by any other class than the collection which contains the node.  The collection bears the responsibility
     * of rationalizing/ordering the indices within the collection.  Nodes are unaware of their siblings in
     * a direct way - they need to ask the parent for the sibling collection
     *
     * @function setIndex
     * @param non-negative-int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }
}
