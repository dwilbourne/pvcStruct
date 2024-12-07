<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\search\NodeVisitableInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\payload\PayloadTrait;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\NodeNotEmptyHydrationException;
use pvc\struct\tree\err\RootCannotBeMovedException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\search\VisitationTrait;

/**
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeDTOInterface
 * @implements TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
class TreenodeAbstract implements TreenodeAbstractInterface, NodeVisitableInterface
{
    /**
     * @use PayloadTrait<PayloadType>
     */
    use PayloadTrait;
    use VisitationTrait;

    /**
     * unique id for this node
     * @var non-negative-int $nodeid
     */
    protected int $nodeid;

    /**
     * reference to parent
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    protected ?TreenodeAbstractInterface $parent;

    /**
     * reference to containing tree
     * @var TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected TreeAbstractInterface $tree;

    /**
     * @var CollectionAbstractInterface<PayloadType, CollectionType> $children
     */
    protected CollectionAbstractInterface $children;


    /**
     * @param CollectionAbstractInterface<PayloadType, CollectionType> $collection
     * @param PayloadTesterInterface<PayloadType> $tester
     * @throws ChildCollectionException
     */
    public function __construct(CollectionAbstractInterface $collection, PayloadTesterInterface $tester = null)
    {
        /**
         * set the child collection
         */
        if (!$collection->isEmpty()) {
            throw new ChildCollectionException();
        } else {
            $this->children = $collection;
        }

        /**
         * set the tester
         */
        $this->setPayloadTester($tester);
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
     * hydrate
     * @param TreenodeDTOInterface<PayloadType, ValueObjectType> $dto
     * @param TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $tree
     * @throws AlreadySetNodeidException
     * @throws CircularGraphException
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @throws RootCannotBeMovedException
     * @throws SetTreeIdException
     */
    public function hydrate(TreenodeDTOInterface $dto, TreeAbstractInterface $tree): void
    {
        /**
         * cannot hydrate a node if it already has been hydrated
         */
        if (!$this->isEmpty()) {
            throw new NodeNotEmptyHydrationException($this->getNodeId());
        }

        $nodeId = $dto->nodeId;
        $parentId = $dto->parentId;
        $treeId = $dto->treeId;

        /**
         * nodeid must be non-negative.  phpstan will catch static problems but to be thorough, let's catch it anyway
         */
        if ($nodeId < 0) {
            throw new InvalidNodeIdException($nodeId);
        }

        /**
         * node id cannot already exist in the tree
         */
        if ($tree->getNode($nodeId)) {
            throw new AlreadySetNodeidException($nodeId);
        }
        $this->nodeid = $nodeId;

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
         * setParent also sets the "reciprocal pointer" by adding this node to the child collection of the parent
         */
        $this->setParent($parentId);

        /**
         * set the payload - the setPayload method validates the payload before setting it
         */
        $this->setPayload($dto->payload);
    }

    /**
     * @function setParent
     * @param non-negative-int|null $parentId
     */
    public function setParent(?int $parentId): void
    {
        if (!is_null($parentId)) {
            /**
             * @phpcs:ignore
             * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null $parent
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
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    public function getParent(): ?TreenodeAbstractInterface
    {
        return $this->parent ?? null;
    }

    /**
     * @function getTree
     * @return TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
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
     * @return CollectionAbstractInterface<PayloadType, CollectionType>
     */
    public function getChildren(): CollectionAbstractInterface
    {
        return $this->children;
    }

    /**
     * getChildrenAsArray
     * @return array<int, PayloadType>
     */
    public function getChildrenAsArray(): array
    {
        return $this->getChildren()->getElements();
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
     * @function hasChildren
     * @return bool
     */
    public function hasChildren(): bool
    {
        return (!$this->getChildren()->isEmpty());
    }

    /**
     * @function getChild
     * @param non-negative-int $nodeid
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
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
     * @return CollectionAbstractInterface<PayloadType, CollectionType>
     */
    public function getSiblings(): CollectionAbstractInterface
    {
        /**
         * the root has no siblings of course.  It is easier to create a collection here than to deal with multiple
         * data types down the road for whoever wants to get the siblings of root.  But we do have to go a long
         * way to get to the collection factory......
         */
        if ($this->getTree()->rootTest($this)) {
            $collectionFactory = $this->getTree()->getTreenodeFactory()->getCollectionFactory();
            /** @var CollectionType $collection */
            $collection = $collectionFactory->makeCollection();
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
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @return bool
     */
    public function isAncestorOf(TreenodeAbstractInterface $node): bool
    {
        return $node->isDescendantOf($this);
    }

    /**
     * @function isDescendantOf
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
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
}
